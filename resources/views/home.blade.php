@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header">Task's List</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#createTaskModal">
              <span class="glyphicon glyphicon-plus"></span> Task
            </button>
        </div>
        <div class="col-sm-6 text-right">

            <div class="dropdown">
              <button class="btn btn-info dropdown-toggle btn-lg" type="button" data-toggle="dropdown">Filter
              <span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="/">Default</a></li>
                <li role="separator" class="divider"></li>

                <li {!! Request::input('filter') == 'progressing' ? 'class="active"' : ''  !!}><a href="?filter=progressing">In Progress</a></li>
                <li {!! Request::input('filter') == 'done' ? 'class="active"' : ''  !!}><a href="?filter=done">Done</a></li>
                <li {!! Request::input('filter') == 'complete' ? 'class="active"' : ''  !!}><a href="?filter=complete">Complete</a></li>
              </ul>
            </div>
        </div>
    </div>
    <br />
    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- createTaskModal -->
    <div class="modal fade" id="createTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Create Task</h4>
          </div>

          <form method="POST" action="{{route('public::create.task')}}" accept-charset="UTF-8" style="display:inline">
              {{ csrf_field() }}
              <div class="modal-body">
                  <label for="title">Task Name</label>
                  <input type="text" class="form-control" name="title" id="title" placeholder="required">
                  <br>
                  <label for="parent_id">Parant Task ID</label>
                  <input type="number" class="form-control" name="parent_id" id="parent_id" placeholder="optional">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Add</button>
              </div>
          </form>
        </div>
      </div>
    </div><!-- createTaskModal -->

    <!-- editTaskModal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Edit Task #<span id="edit_task_id"></span></h4>
          </div>

          <form method="POST" action="{{route('public::update.task')}}" accept-charset="UTF-8" style="display:inline">
              {{ csrf_field() }}
              <div class="modal-body">
                  <input type="hidden" name="task_id" value="">
                  <label for="title">Task Name</label>
                  <input type="text" class="form-control" name="title" id="title" placeholder="required">
                  <br>
                  <label for="parent_id">Parant Task ID</label>
                  <input type="number" class="form-control" name="parent_id" id="parent_id" placeholder="optional">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Edit</button>
              </div>
          </form>
        </div>
      </div>
  </div><!-- editTaskModal -->

    @if (count($tasks) > 0)
        <ul class="list-group">
        @foreach ($tasks as $task)
            @include('partials.tasklists', $task)
        @endforeach
        </ul>
    @endif
        <div class="pagination pull-right"> {{ $tasks->links() }} </div>

@endsection
@section('script')
<script>
    $( document ).ready(function() {
        $('input[class="task-checked"]').click(function () {
            var task_id = this.value;

            if ($(this).prop('checked')) {
                $.ajax({
                  url: "{{route('public::set.task.completed')}}",
                  type: "post",
                  data: {
                      "_token": "{{ csrf_token() }}",
                      "id": task_id
                  },
                  success: function(data){
                      window.location.reload();
                  }
                });
            }else{
                $.ajax({
                  url: "{{route('public::set.task.progress')}}",
                  type: "post",
                  data: {
                      "_token": "{{ csrf_token() }}",
                      "id": task_id
                  },
                  success: function(data){
                      window.location.reload();
                  }
                });
            }
        });

        $('#editTaskModal').on('show.bs.modal', function(e) {
            //get data-id attribute of the clicked element
            var id = $(e.relatedTarget).data('id');
            var parent_id = $(e.relatedTarget).data('parent-id');
            var title = $(e.relatedTarget).data('title');

            if (parent_id == 0) parent_id = null;
            //populate the textbox
            $("#edit_task_id").text(id);
            $(e.currentTarget).find('input[name="task_id"]').val(id);
            $(e.currentTarget).find('input[name="title"]').val(title);
            $(e.currentTarget).find('input[name="parent_id"]').val(parent_id);
        });
    });

</script>
@endsection
