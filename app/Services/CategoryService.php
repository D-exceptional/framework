<?php

namespace App\Services;

use App\Http\Response;
use App\Models\Category;

class CategoryService
{
    protected Response $response;
    protected Category $categoryModel;

    public function __construct(
        Response $response, 
        Category $categoryModel
    )
    {
        $this->response      = $response;
        $this->categoryModel = $categoryModel;
    }

    // =========================================
    // GET ALL CATEGORIES
    // =========================================
    public function all(): array
    {
        $categories = $this->categoryModel->all();

        // Empty cart is NOT an error
        return $this->response->success('All categories fetched', ['categories' => $categories]);
    }

    // =========================================
    // CREATE CATEGORY
    // =========================================
    public function create(
        string $category
    ): array {

        $created = $this->categoryModel->create($category);
        if ($created === false) {
            return $this->response->fail('Failed to create category', [], 500);
        }

        return $this->response->success('Category created successfully', [], 201);
    }

    // =========================================
    // UPDATE CATEGORY
    // =========================================
    public function update(
        string $name, 
        int $id
    ): array {

        $updated = $this->categoryModel->update($name, $id);
        if ($updated === false) {
            return $this->response->fail('Failed to update category', 500);
        }

        return $this->response->success('Category updated successfully');
    }

    // =========================================
    // DELETE CATEGORY
    // =========================================
    public function delete(
        int $id
    ): array { 

        $deleted = $this->categoryModel->delete($id);
        if ($deleted === false) {
            return $this->response->fail('Failed to delete category', 500);
        }
        
        return $this->response->success('Category deleted successfully');
    }

    // =========================================
    // COUNT CATEGORIES
    // =========================================
    public function count(): array
    { 
        $count = $this->categoryModel->count();
        
        return $this->response->success('Categories counted', ['count' => $count]);
    }
}
