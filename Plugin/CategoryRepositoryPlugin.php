<?php

namespace AnSyS\EmptyConvert\Plugin;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class CategoryRepositoryPlugin
{
    public function aroundSave(CategoryRepository $subject,callable $proceed,CategoryInterface $category)
    {
        $storeId = (int)$subject->storeManager->getStore()->getId();
        $existingData = $subject->getExtensibleDataObjectConverter()
            ->toNestedArray($category, [], CategoryInterface::class);
        $existingData = array_diff_key($existingData, array_flip(['path', 'level', 'parent_id']));
        $existingData['store_id'] = $storeId;

        if ($category->getId()) {
            $metadata = $subject->getMetadataPool()->getMetadata(
                CategoryInterface::class
            );

            $category = $subject->get($category->getId(), $storeId);
            $existingData[$metadata->getLinkField()] = $category->getData(
                $metadata->getLinkField()
            );

            if (isset($existingData['image']) && is_array($existingData['image'])) {
                if (!empty($existingData['image']['delete'])) {
                    $existingData['image'] = null;
                } else {
                    if (isset($existingData['image'][0]['name']) && isset($existingData['image'][0]['tmp_name'])) {
                        $existingData['image'] = $existingData['image'][0]['name'];
                    } else {
                        unset($existingData['image']);
                    }
                }
            }
        } else {
            $parentId = $category->getParentId() ?: $subject->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $subject->get($parentId, $storeId);
            $existingData['path'] = $parentCategory->getPath();
            $existingData['parent_id'] = $parentId;
            $existingData['level'] = null;
        }
        $subject->populateWithValues->execute($category, $existingData);
/*
        try {
            $subject->validateCategory($category);
            $subject->categoryResource->save($category);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save category: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
*/
        $this->saveCategoryData($subject,$category, $existingData); 
        unset($subject->instances[$category->getId()]);
        return $subject->get($category->getId(), $storeId);
    }

   /**
     * Save category data
     *
     * @param CategoryRepository $subject
     * @param Category $category
     * @param string[] $dataToSave
     * @return void
     * @throws CouldNotSaveException
     */
    protected function saveCategoryData(CategoryRepository $subject,Category $category, $dataToSave)
    {
        try {
            $subject->validateCategory($category);
            if ($category->getId()) {
                foreach (array_keys($dataToSave) as $attribute) {
                    if (!in_array($attribute, ['entity_id', 'id', 'store_id'])) {
                        $subject->categoryResource->saveAttribute($category, $attribute);
                    }
                }
            } else {
                $subject->categoryResource->save($category);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save category: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }
}
