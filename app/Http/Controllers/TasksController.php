<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Tasks;

class TasksController extends Controller
{
    const DEFAULT_PARENT_ID   = 0;
    protected $tasks_per_page = 20;
    private $task_status = [ '0' =>  "in progress", '1' =>  "done", '2' =>  "complete" ];

    /**
     * Task's list home page
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        if($request->has('filter')){
            switch ($request->filter) {
                case "progressing":
                    $tasks = Tasks::where('parent_id', self::DEFAULT_PARENT_ID )->isProgressing()->paginate($this->tasks_per_page);
                    $tasks->setPath('?filter=progressing');
                    $tasks = $this->countSubTasksStatus($tasks);
                    break;
                case "done":
                    $tasks = Tasks::where('parent_id', self::DEFAULT_PARENT_ID )->isDone()->paginate($this->tasks_per_page);
                    $tasks->setPath('?filter=done');
                    $tasks = $this->countSubTasksStatus($tasks);
                    break;
                case "complete":
                    $tasks = Tasks::where('parent_id', self::DEFAULT_PARENT_ID )->isCompleted()->paginate($this->tasks_per_page);
                    $tasks->setPath('?filter=complete');
                    $tasks = $this->countSubTasksStatus($tasks);
                    break;
                default:
                    abort(401);;
            }
                return view('home')->with(['tasks' => $tasks ]);
        }

        $tasks = Tasks::where('parent_id', self::DEFAULT_PARENT_ID )->paginate($this->tasks_per_page);
        $tasks = $this->countSubTasksStatus($tasks);
        return view('home')->with(['tasks' => $tasks ]);
    }

    /**
     * create task
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function createTask(Request $request){

        $messages = [
            'title.required'            => 'Task Name are require in order to create task.',
            'parent_id.exists_or_null'  => 'Task ID does not exist.'
        ];

        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'parent_id' => 'integer|exists_or_null:tasks,id'
        ],$messages);

        if ($validator->fails()) {
           return redirect()->route('public::home')->withErrors($validator)->withInput();
        }

        $parent_id = empty($request->parent_id) ? self::DEFAULT_PARENT_ID : $request->parent_id;

        Tasks::create([
            'title' => $request->title,
            'parent_id' => $parent_id
        ]);

        return back();
    }

    /**
     * update task
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateTask(Request $request){

        $messages = [
            'task_id.required'          => 'Task ID not found',
            'title.required'            => 'Task Name are require in order to create task.',
            'parent_id.exists_or_null'  => 'Task ID does not exist.'
        ];

        $validator = Validator::make($request->all(), [
            'task_id'   => 'required',
            'title'     => 'required',
            'parent_id' => 'integer|exists_or_null:tasks,id'
        ],$messages);

        if ($validator->fails()) {
           return redirect()->route('public::home')->withErrors($validator)->withInput();
        }

        $task = Tasks::find($request->task_id);

        if(!empty($request->parent_id)){
            $will_trigger_circular_dependencies = $this->willTriggerCircularDependencies($task, $request->parent_id);
            if($will_trigger_circular_dependencies){
                return back()->withErrors("Invalid Parent ID, this will cause circular dependencies");
            }
        }

        $pevious_parent_id = $task->parent_id;
        $new_parent_id = (int)$request->parent_id;
        $task->parent_id = $new_parent_id;
        $task->title = $request->title;
        $task->update();

        if($task->update()){
            if($task->status != Tasks::TASK_IS_COMPLETED ){
                $this->updateParentTaskAfterEdit($pevious_parent_id, $new_parent_id);
            }
            return back();
        }

        abort(401);
    }

    /**
     * set task status to done
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function setTaskToCompleted(Request $request){

        if($request->ajax()) {
            $task = Tasks::where('id', $request->id)->first();
            $task->status = Tasks::TASK_IS_COMPLETED;

            $is_completed_tasks = $this->isCompletedTasks($task);

            if(!$is_completed_tasks){
                $task->status = Tasks::TASK_IS_DONE;
            }

            $task->update();

            if($task->update()){
                $this->setParentTaskToCompleted($task);
                return response()->json($task);
            }
        }

        abort(401);
    }

    /**
     * set task status to progress
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function setTaskToProgress(Request $request) {

        if($request->ajax()) {
            $task = Tasks::where('id', $request->id)->first();
            $task->status = Tasks::TASK_IS_PROGRESSING;
            $task->update();

            if($task->update()){
                $this->setParentTaskToDone($task);
                return response()->json($task);
            }
        }

        abort(401);
    }

    /**
     * identify the task is belong to complete
     * @param  Tasks   $task
     * @param  boolean $result
     * @return boolean $result
     */
    protected function isCompletedTasks($task, $result = true){

        if(count($task->childrenRecursive)){
            foreach ($task->childrenRecursive as $item) {
                if($item->status == Tasks::TASK_IS_PROGRESSING) $result = false;

                if(count($item->childrenRecursive)){
                    $result = $this->isCompletedTasks($item, $result);
                }
            }
        }

        return $result;
    }

    /**
     * set parent task to completed if all sub-task completed
     * @param collection $task
     * @return void
     */
    protected function setParentTaskToCompleted($task){

        if($task->status == Tasks::TASK_IS_DONE){
            $is_completed_tasks = $this->isCompletedTasks($task);

            if($is_completed_tasks){
                $task->status = Tasks::TASK_IS_COMPLETED;
                $task->update();
            }
        }

        if(count($task->parentRecursive)){
            $this->setParentTaskToCompleted($task->parentRecursive);
        }
    }

    /**
     * if sub-task status = "in progress", set parent task to done
     * @param collection $task
     * @return void
     */
    protected function setParentTaskToDone($task){

        if(count($task->parentRecursive)){
            $task = $task->parentRecursive;
            if($task->status == Tasks::TASK_IS_COMPLETED){
                $task->status = Tasks::TASK_IS_DONE;
                $task->update();
            }

            if(count($task->parentRecursive)){
                $this->setParentTaskToDone($task);
            }
        }
    }

    /**
     * update parent task status after edit
     * @param  int $pevious_parent_id
     * @param  int $new_parent_id
     * @return void
     */
    private function updateParentTaskAfterEdit($pevious_parent_id, $new_parent_id){

        if($pevious_parent_id != self::DEFAULT_PARENT_ID){
            $pevious_parent = Tasks::find($pevious_parent_id);
            $is_completed_tasks = $this->isCompletedTasks($pevious_parent);
            if($is_completed_tasks){
                $this->setParentTaskToCompleted($pevious_parent);
            }
        }

        if($new_parent_id != self::DEFAULT_PARENT_ID){
            $new_parent_id  = Tasks::find($new_parent_id);
            if($new_parent_id->status == Tasks::TASK_IS_COMPLETED){
                $new_parent_id->status = Tasks::TASK_IS_DONE;
                $new_parent_id->update();
                $this->setParentTaskToDone($new_parent_id);
            }
        }
    }

    /**
     *
     * @param  Tasks $task
     * @param  Integer $new_parent_id
     * @return boolean
     */
    private function willTriggerCircularDependencies($task, $new_parent_id){

        if ($new_parent_id == Tasks::TASK_IS_PROGRESSING) return false;
        if ($task->parent_id == $new_parent_id) return false;
        if ($task->id == $new_parent_id) return true;

        $new_parent_task = Tasks::find($new_parent_id);

        if(count($new_parent_task->parentRecursive)) {
            function newParentIdIsSameAsCurrentTaskId($new_parent_task, $task_id, $had_task_id = false){

                if($new_parent_task->parent_id == $task_id) $had_task_id = true;

                if(count($new_parent_task->parentRecursive)){
                    $had_task_id = newParentIdIsSameAsCurrentTaskId($new_parent_task->parentRecursive, $task_id, $had_task_id);
                }
                return $had_task_id;
            }

            return newParentIdIsSameAsCurrentTaskId($new_parent_task, $task->id);
        }

        return false;
    }

    /**
     * count sub Taks status
     * @param  Tasks $tasks
     * @return Tasks $tasks
     */
    private function countSubTasksStatus($tasks) {

        foreach ($tasks as $item) {
            $all_children= $item->getAllchildren();
            if(count($all_children)){
                $item->total_sub_task = count($all_children);

                $task['sub_task_status'] = ['in_progress'=> 0, 'done'=> 0, 'complete'=> 0 ];
                foreach ($all_children as $sub_task) {
                    switch($sub_task->status){
                        case Tasks::TASK_IS_PROGRESSING:
                            $task['sub_task_status']['in_progress']++;
                            break;
                        case Tasks::TASK_IS_DONE:
                            $task['sub_task_status']['done']++;
                            break;
                        case Tasks::TASK_IS_COMPLETED:
                            $task['sub_task_status']['complete']++;
                            break;
                    }
                }
                $item->sub_task_status = $task['sub_task_status'];
            }
        }

        $tasks = $this->changeTaskStatusToWordStatement($tasks);
        return $tasks;
    }

    /**
     * change task status to Word statement

     * @param  Tasks $tasks
     * @return Tasks $tasks
     */
    private function changeTaskStatusToWordStatement($tasks){

        foreach ($tasks as $item) {
            $item->status = $this->task_status[$item->status];
            if (count($item->childrenRecursive)) {
                $this->changeTaskStatusToWordStatement($item->childrenRecursive);
            }
        }
        return $tasks;
    }

    /**
     * no longer needed, apply recursive logic in task model
     * http://stackoverflow.com/questions/2273449/creating-a-multilevel-array-using-parentids-in-php
     * @param  Array  $ar
     * @param  integer $pid
     * @return Array
     */
    // protected function buildTree( $ar, $pid = 0 ) {
    //     $op = [];
    //     foreach( $ar as $item ) {
    //         if( $item['parent_id'] == $pid ) {
    //             $op[$item['id']] = [
    //                 'title' => $item['title'],
    //                 'status' => $item['status'],
    //                 'parent_id' => $item['parent_id'],
    //             ];
    //             // using recursive
    //             $children =  $this->buildTree( $ar, $item['id'] );
    //             if( $children ) {
    //                 $op[$item['id']]['children'] = $children;
    //             }
    //         }
    //     }
    //     return $op;
    // }
}
