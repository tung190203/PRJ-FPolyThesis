@extends('admin.layouts.app')
@section('title')
    {{ $title }}
@endsection
@section('content')
    @push('css')
        <link rel="stylesheet" href="../../bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
        <style>
            .example-modal .modal {
                position: relative;
                top: auto;
                bottom: auto;
                right: auto;
                left: auto;
                display: block;
                z-index: 1;
            }

            .example-modal .modal {
                background: transparent !important;
            }
        </style>
    @endpush
    @php
        $userGroupId = auth()->user()->group_id;
        $isSPAdmin = $userGroupId == config('default.user.groupID.superAdmin') ? true : false;
        if (!$isSPAdmin) {
            $role_id = App\Models\UserRole::where('user_id', Auth::user()->id)->first()->role_id;
            if (!is_null($role_id)) {
                $userPermission = App\Models\RolePermission::getUserPermistion($role_id);
            }
        }
    @endphp
    <div class="row">
        <div class="col-xs-12 mx-5">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Danh sách blog</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    {{-- <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary mb-3">Tạo mới</a> --}}
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Tiêu đề</th>
                                <th>Người tạo</th>
                                <th>Chuyên ngành</th>
                                <th>Thao tác</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($blogs as $blog)
                                <tr>
                                    <td>{{ $blog->title }}</td>
                                    <td>
                                        @if ($blog->user && $blog->user->username)
                                            {{ $blog->user->username }}
                                        @else
                                            Chưa có
                                        @endif
                                    </td>
                                    <td>
                                        @if ($blog->major && $blog->major->majors_name)
                                            {{ $blog->major->majors_name }}
                                        @else
                                            Chưa có
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.blogs.show', $blog->id) }}" class="btn btn-info btn-sm"><i
                                                class="fa fa-eye"></i></a>

                                        @if ($blog->status == config('default.blog.status.approved'))
                                            @if ($isSPAdmin || in_array('admin.blogs.statusApprove', $userPermission))
                                                <button type="submit" class="btn btn-danger btn-sm" data-toggle="modal"
                                                    data-target="#modal-danger-{{ $blog->id }}"><i
                                                        class="fa fa-trash-o"></i></button>
                                            @endif
                                        @endif

                                        <div class="modal modal-danger fade" id="modal-danger-{{ $blog->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title">FpolyZone</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Bạn có chắc muốn xóa {{ $blog->title }}? </p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline pull-left"
                                                            data-dismiss="modal">Hủy</button>
                                                        <form action="{{ route('admin.blogs.destroy', $blog->id) }}"
                                                            method="POST" style="display:inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline">Đồng ý</button>
                                                        </form>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    @push('js')
        <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
        <script>
            $(function() {
                $('#example1').DataTable()
                $('#example2').DataTable({
                    'paging': true,
                    'lengthChange': false,
                    'searching': false,
                    'ordering': true,
                    'info': true,
                    'autoWidth': false
                })
            })
        </script>
    @endpush
@endsection
