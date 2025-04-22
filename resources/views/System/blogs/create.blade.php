@extends('layouts.admin.master')
@section('Thêm bài viết')
@section('content')
    <div class="card-body">

        <h5 class="card-title fw-semibold mb-4 ">Thêm bài viết</h5>

        <div class="card w-100">
            <div class="card-body p-4">
                <form action="{{ route('system.blogs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="table-responsive ms-3">
                        <div class="col-md-12 row">
                            <div class="mb-3">
                                <label for="roleSelect" class="form-label">Tiêu đề</label>
                                <input type="text" id="titleInput" name="title" class="form-control"
                                    placeholder="Tiêu đề" value="{{ old('title') }}">
                                @error('title')
                                    <div class="text-danger">*{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12 row" id="inputFields">
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="" class="form-label">Nội dung</label>
                                    <textarea id="summernote" name="content">{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="text-danger">*{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="" class="form-label">Ảnh đại diện</label>
                                    <input type="file" class="filepond" name="thumbnail">
                                    @error('thumbnail')
                                        <div class="text-danger">*{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="describeTextarea" class="form-label">Mô tả</label>
                                    <textarea id="describeTextarea" name="describe" class="form-control" placeholder="Mô tả" rows="4"
                                        style="overflow-y: scroll;">{{ old('describe') }}</textarea>
                                    @error('describe')
                                        <div class="text-danger">*{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="statusSelect" name="status"
                                        onchange="toggleDateInput()">
                                        <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Chờ xuất bản
                                        </option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Xuất bản</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="dateContainer" style="display:none;">
                                    <label for="" class="form-label">Ngày xuất bản</label>
                                    <input type="date" id="dateInput" name="date" class="form-control"
                                        value="{{ old('date') }}">
                                    @error('date')
                                        <div class="text-danger">*{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Tác giả</label>
                                    <input type="text" id="authorInput" name="author" readonly class="form-control"
                                        value="{{ $user->lastname }} {{ $user->firstname }}" placeholder="Tác giả">
                                    @error('author')
                                        <div class="text-danger">*{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const editorHeight = $(window).height() - 200;

                $('#summernote').summernote({
                    styleTags: ['h2', 'h3', 'h4', 'h5', 'h6'],
                    codemirror: {
                        theme: 'monokai'
                    },
                    lang: 'vi-VN',
                    placeholder: 'Nhập nội dung....',
                    height: editorHeight > 0 ? editorHeight : 200,
                    focus: true,
                    fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Times New Roman'],
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['fontsize', ['fontsize']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['height', ['height']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview', 'help']],
                        ['style', ['style']],
                    ],
                    popover: {
                        image: [
                            ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                            ['float', ['floatLeft', 'floatRight', 'floatNone']],
                            ['remove', ['removeMedia']]
                        ],
                        link: [
                            ['link', ['linkDialogShow', 'unlink']]
                        ],
                        table: [
                            ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                            ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
                        ],
                        air: [
                            ['color', ['color']],
                            ['font', ['bold', 'underline', 'clear']],
                            ['para', ['ul', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']]
                        ]
                    },
                    callbacks: {
                        onImageUpload: function(files) {
                            if (files.length > 1) {
                                alert("Chỉ có thể chọn một ảnh mỗi lần.");
                                return;
                            }

                            // Đếm ảnh hiện tại
                            const imageCount = countImagesInEditor();
                            if (imageCount >= 8) {
                                alert("Bạn chỉ có thể chèn tối đa 8 ảnh.");
                                return;
                            }

                            const file = files[0];
                            if (file.size > 500 * 1024) {
                                alert("Kích thước tệp phải nhỏ hơn 500KB.");
                                return;
                            }


                            const reader = new FileReader();
                            reader.onload = (e) => {
                                $('#summernote').summernote('insertImage', e.target.result);
                            };
                            reader.readAsDataURL(file);
                        },
                        onMediaDelete: function($target) {
                            console.log('Hình ảnh đã bị xóa:', $target[0].src);
                        }
                    }
                });

                $('.note-btn-image').click(function() {
                    const input = $('<input type="file" accept="image/*" style="display:none;">');
                    input.on('change', function(event) {
                        const files = event.target.files;

                        // Đếm ảnh hiện tại
                        const imageCount = countImagesInEditor();
                        if (imageCount >= 8) {
                            alert("Bạn chỉ có thể chèn tối đa 8 ảnh.");
                            $(this).remove();
                            return;
                        }

                        const file = files[0];
                        if (file.size > 500 * 1024) {
                            alert("Kích thước tệp phải nhỏ hơn 500KB.");
                            $(this).remove();
                            return;
                        }

                        $('#summernote').summernote('insertImage', URL.createObjectURL(file));
                        $(this).remove();
                    });

                    $('body').append(input);
                    input.trigger('click');
                });

                function countImagesInEditor() {
                    const content = $('#summernote').summernote('code');
                    return $(content).find('img').length;
                }
            });
        </script>
        <script>
            const inputElement = document.querySelector('input[name="thumbnail"]');
            const pond = FilePond.create(inputElement);

            @if (session('upload_file') && $errors->any())
                // Lấy chuỗi base64 từ session
                const base64String = '{{ session('upload_file') }}'; // Giả sử chuỗi base64 đã được lưu trong session

                // Đặt loại MIME dựa trên định dạng của ảnh
                const mimeString = "image/jpeg"; // Hoặc "image/png" tùy vào định dạng thực tế

                // Tạo chuỗi base64 đầy đủ với tiền tố dữ liệu
                const fullBase64String = `data:${mimeString};base64,${base64String}`;

                // Chuyển đổi base64 thành Blob
                const byteString = atob(fullBase64String.split(',')[1]); // Tách phần base64
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);

                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }

                const blob = new Blob([ab], {
                    type: mimeString
                });

                // Tạo file từ Blob
                const file = new File([blob], "thumbnail." + (mimeString === 'image/jpeg' ? 'jpg' : 'png'), {
                    type: mimeString
                });

                // Thêm tệp vào FilePond
                pond.addFile(file).then(() => {
                    console.log('File thumbnail added successfully');
                }).catch(error => {
                    console.error('Error adding thumbnail:', error);
                });
            @endif

            pond.setOptions({
                server: {
                    process: {
                        url: './uploadfile',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    },
                    revert: {
                        url: './revertfile',
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                },
                labelIdle: `Tối đa 1MB <span class="filepond--label-action">Chọn tệp</span>`,
                acceptedFileTypes: ['image/jpeg', 'image/png'],
                maxFileSize: 1 * 1024 * 1024, // 1.5MB in bytes
                labelMaxFileSize: '1MB',
                imagePreviewHeight: 200,
                // instantUpload: false,

            });
        </script>
        <script>
            function toggleDateInput() {
                var statusSelect = document.getElementById('statusSelect');
                var dateContainer = document.getElementById('dateContainer');

                if (statusSelect.value == "1") {
                    dateContainer.style.display = "block";
                } else {
                    dateContainer.style.display = "none";
                }
            }

            function setMinDate() {
                var dateInput = document.getElementById('dateInput');
                var tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);

                var dd = String(tomorrow.getDate()).padStart(2, '0');
                var mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
                var yyyy = tomorrow.getFullYear();

                var minDate = yyyy + '-' + mm + '-' + dd;
                dateInput.setAttribute('min', minDate);
            }

            window.onload = function() {
                setMinDate();
                toggleDateInput();
            };
        </script>
    @endpush
@endsection
