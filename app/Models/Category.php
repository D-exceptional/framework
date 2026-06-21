<?php

namespace App\Models;

class Category extends Model
{
    protected string $table = 'blog_categories';

    // =========================================
    // LIST ALL CATEGORIES
    // =========================================
    public function all()
    : ?array {

        return $this->query()
            ->select(['category_name'])
            ->whereNotNull('category_name')
            ->orderBy('category_name', 'ASC')
            ->get();
    }


    // =========================================
    // CREATE CATEGORY
    // =========================================
    public function create(
       string $category
    ): bool {
        
        return $this->query()
            ->insert(['category_name' => $category]);
    }

    // =========================================
    // UPDATE CATEGORY
    // =========================================
    public function update(
        string $name, 
        int $categoryId
    ): bool {

        return $this->query()
            ->where('category_id', '=', $categoryId)
            ->update(['category_name' => $name]);
    }

    // =========================================
    // DELETE CATEGORY
    // =========================================
    public function delete(
        int $categoryId
    ): bool {

        return $this->query()
            ->where('category_id', '=', $categoryId)
            ->delete();
    }

    // =========================================
    // COUNT ALL CATEGORY
    // =========================================
    public function count()
    : int {

        return $this->query()
            ->count();
    }
}
