<?php

namespace Blog\Service;

interface categoryServiceInterface {

    /**
     *
     * Create new Category object
     *
     * @return      object
     *
     */
    public function createCategory();

    /**
     *
     * Get array of categories
     *
     * @return      array
     *
     */
    public function getCategories();

    /**
     *
     * Get category object based on id
     *
     * @param       id  $id The id to fetch the category from the database
     * @return      object
     *
     */
    public function getCategoryById($id);

    /**
     *
     * Create form of an object
     *
     * @param       category $category
     * @return      form
     *
     */
    public function createCategoryForm($category);

    /**
     *
     * Save category to database
     *
     * @param       category object
     * @return      void
     *
     */
    public function storeCategory($category);

    /**
     *
     * Delete a category object from database
     * @param       category $category object
     * @return      void
     *
     */
    public function deleteCategory($category);
}
