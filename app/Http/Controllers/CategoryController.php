<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
      return view('category.index', [
        'categories' => Category::all(),
      ]);
    }

    public function show(Category $category)
    {
      return view('category.show', [
        'category' => $category->name,
        'books' => $category->books,
      ]);
    }
}
