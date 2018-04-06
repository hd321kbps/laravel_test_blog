<?php

namespace App;

use App\User;
use Carbon\Carbon;
// Подключение Storage
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
// Подключение slug 1
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model
{
    // Подключение slug 2
    use Sluggable;
    const IS_DRAFT = 0;
    const IS_PUBLIC = 1;
    protected $fillable = ['title', 'content', 'date', 'description'];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function tags()
    {
        // Связь один ко многим
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',
            'tag_id'        
        );
    }
    // Подключение slug 3
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }
    public static function add($fields)
    {
        // Todo сделать регистрации
        $post = new static; // создание экземпляра класса
        $post->fill($fields); // вывод из масива fillable
        // Текущий Auth::user пользователя
        $post->user_id = Auth::user()->id; // задаем админи, чтобы никто не мог занять id
        $post->save();
        return $post; // возвращаем пост
    }
    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }
    public function remove()
    {
        // Todo удалить картинку поста
        $this->removeImage();
        $this->delete();
    }
    public function removeImage() {
        if($this->image != null){
            Storage::delete('uploads/'. $this->image);
        }
    }
    public function uploadImage($image)
    {
        if($image == null) {return;} // проверка если не пришла картинка
        $this->removeImage(); // Удаляем картинку если она есть
        $filename = str_random(10).'.'.$image->extension(); // генерируем имя файла
        $image->storeAs('uploads', $filename); // сохраняем в папку
        $this->image = $filename; // выводим картинку
        $this->save();
    }
    public function getImage()
    {
        if($this->image == null)
        {
            return '/img/no-image.png'; // если нет то картинка по-умолчанию
        }
        return '/uploads/' . $this->image; // получаем картинку
    }
    public function setCategory($id)
    {
        if($id == null) {return;}
        $this->category_id = $id; // Получаем id категории
        $this->save();
    }    
    public function setTags($ids)
    {
        if($ids == null) {return;}
        $this->tags()->sync($ids); // Синхронизируем теги с постом
    }
    public function setDraft()
    {
        $this->status=Post::IS_DRAFT; // через константы 1 способ
        $this->save();
    }
    public function setPublic()
    {
        $this->status=Post::IS_PUBLIC;
        $this->save();
    }
    public function toggleStatus($value)
    {
        if($value == null)
        {
            return $this->setDraft();
        }
        return $this->setPublic();
    }
    public function setFeatured()
    {
        $this->is_featured=1; // через значения 2 способ
        $this->save();
    }
    public function setStandart()
    {
        $this->is_featured=0;
        $this->save();
    }
    public function toggleFeatured($value)
    {
        if($value == null)
        {
            return $this->setStandart();
        }
        return $this->setFeatured();
    }
    public function setDateAttribute($value){
        $date = Carbon::createFromFormat('d/m/y', $value)->format('Y-m-d');
        $this->attributes['date']=$date;
    }
    public function getDateAttribute($value){
        $date = Carbon::createFromFormat('Y-m-d', $value)->format('d/m/y');
        return $date;
    }
    public function getCategoryTitle(){
//        if($this->category != null){
//            return $this->category->title;
//        }
//        return 'Нет категории';
        return ($this->category != null)
            ? $this->category->title
            : 'Нет категории';
    }
    public function getTagsTitles(){
        return (!$this->tags->isEmpty())
            ? implode (', ', $this->tags->pluck('title')->all())
            : 'Нет тегов';
    }
    public function getCategoryID(){
        return $this->category != null ? $this->category->id : null;
    }
    public function getDate(){
        return Carbon::createFromFormat('d/m/y', $this->date)->format('F d, Y');
    }
    public function hasPrevious(){
        // id 4 in 5
        return self::where('id', '<', $this->id)->max('id');
    }
    public function getPrevious(){
        $postID = $this->hasPrevious(); // получаем ID
        return self::find($postID);
    }
    public function hasNext(){
        // id 5 in 4
        return self::where('id', '>', $this->id)->min('id');
    }
    public function getNext(){
        $postID = $this->hasNext(); // получаем ID
        return self::find($postID);
    }
    public function related(){
        return self::all()->except($this->id);
    }
    public function hasCategory(){
        return $this->category != null ? true : false;
    }
    public static function getPopularPosts(){
        return self::orderBy('views', 'desc')->take(3)->get();
    }
}
