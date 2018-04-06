<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Post;
use App\Category;
use Illuminate\Http\Request;


class HomeController extends Controller
{
    public function index(){
        // Проверка на опубликован или нет
        $posts = Post::where('status', Post::IS_PUBLIC)->paginate(2);
//        $popularPosts = Post::orderBy('views', 'desc')->take(3)->get();
//        $featuredPosts = Post::where('is_featured', 1)->take(3)->get();
//        $recentPosts = Post::orderBy('date', 'desc')->take(4)->get();
//        $categories = Category::all();
        // $recentPosts = Post::orderBy('date', 'desc')->take(4)->pluck('id')->all();
        //dd($popularPosts);
        return view('pages.index')->with('posts', $posts);
    }
    public function show($slug){
        $post = Post::where('slug', $slug)->firstOrFail();
        return view('pages.show', compact('post'));
    }
    public function tag($slug){
        $tag = Tag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->where('status', 1)->paginate(2);
        return view('pages.list', ['posts' => $posts]);
    }
    public function category($slug){
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->where('status', 1)->paginate(2);
        return view('pages.list', ['posts' => $posts]);
    }
}
