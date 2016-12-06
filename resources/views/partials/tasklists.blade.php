    <li class="list-group-item">
        <span class="label label-pill label-default"># {{ $task->id }}</span> &nbsp;

        <a href="#editTaskModal" data-toggle="modal"
            data-id={{$task->id}}
            data-parent-id={{$task->parent_id}}
            data-title='{{$task->title}}'>
            {{ $task->title }}
        </a> &nbsp;

        <span class="label label-pill label-success"> {{ $task->status }} </span> &nbsp;

        @if (!empty($task->total_sub_task))
            <span class="label label-pill label-primary">{{$task->total_sub_task}} sub-task</span>
        @endif

        @if (!empty($task->sub_task_status))
            @if (!empty($task->sub_task_status['in_progress']))
                <span class="label label-pill label-info">{{$task->sub_task_status['in_progress']}} progressing</span>

            @endif

            @if (!empty($task->sub_task_status['done']))
                <span class="label label-pill label-info">{{$task->sub_task_status['done']}} done</span>

            @endif

            @if (!empty($task->sub_task_status['complete']))
                <span class="label label-pill label-info">{{$task->sub_task_status['complete']}} complete</span>

            @endif
        @endif

        <label class="checkbox-inline pull-right">
            <input class="task-checked" type="checkbox" value={{ $task->id }} @if ($task->status == "done" || $task->status == "complete") checked @endif > Done
        </label>
    </li>

	@if (count($task->childrenRecursive))
	    <ul>
	    @foreach($task->childrenRecursive as $task)
	        @include('partials.tasklists', $task)
	    @endforeach
	    </ul>
	@endif
