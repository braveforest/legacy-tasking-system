<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    const TASK_IS_PROGRESSING   = 0;
    const TASK_IS_DONE          = 1;
    const TASK_IS_COMPLETED     = 2;

    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function scopeIsProgressing( $query ) {
        return $query -> where('status', self::TASK_IS_PROGRESSING);
    }

    public function scopeIsDone( $query ) {
        return $query -> where('status', self::TASK_IS_DONE);
    }

    public function scopeIsCompleted( $query ) {
        return $query->where('status', self::TASK_IS_COMPLETED);
    }

    // loads only direct children - 1 level
    public function children(){
       return $this->hasMany(\App\Models\Tasks::class, 'parent_id');
    }

    /**
     * http://stackoverflow.com/questions/24672629/laravel-orm-from-self-referencing-table-get-n-level-hierarchy-json/24679043#24679043
     * recursive, loads all descendants
     */
    public function childrenRecursive(){
       // which is equivalent to:
       // return $this->hasMany(\App\Models\Tasks::class, 'parent_id')->with('childrenRecursive);
       return $this->children()->with('childrenRecursive');
    }

    // parent
    public function parent(){
       return $this->belongsTo(\App\Models\Tasks::class, 'parent_id');
    }

    // all ascendants
    public function parentRecursive(){
       return $this->parent()->with('parentRecursive');
    }

    /**
    *   http://stackoverflow.com/questions/26652611/laravel-recursive-relationships
    */
    public function getAllchildren(){
        $children = $this->children;
        if (empty($children))
            return $children;

        foreach ($children as $child) {
            $child->load('children');
            $children = $children->merge($child->getAllchildren());
        }

        return $children;
    }

}
