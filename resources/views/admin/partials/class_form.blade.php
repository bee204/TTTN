<div class="class-form-page">
    <header class="class-form-header">
        <div>
            <a href="{{ $cancelRoute }}" class="class-form-back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ $isEdit ? 'Chi tiết lớp Yoga' : 'Danh sách lớp Yoga' }}</a>
            <span class="class-form-eyebrow"><i class="fa-solid fa-circle" aria-hidden="true"></i> {{ $isEdit ? 'Cập nhật lịch tập' : 'Thiết lập lịch tập' }}</span>
            <h1>{{ $isEdit ? 'Chỉnh sửa lớp Yoga' : 'Tạo lớp Yoga mới' }}</h1>
            <p>{{ $isEdit ? 'Cập nhật thông tin vận hành cho lớp '.$class->name.'.' : 'Thiết lập đầy đủ lịch, giáo viên, sức chứa và học phí cho lớp mới.' }}</p>
        </div>
        @if($isEdit)<div class="class-form-meta"><span><i class="fa-solid fa-user-group" aria-hidden="true"></i></span><div><strong>{{ $class->confirmed_registrations_count }}/{{ $class->quantity }} học viên</strong><small>Sĩ số đã xác nhận hiện tại</small></div></div>@endif
    </header>

    <form id="adminClassForm" class="class-form-layout" method="POST" action="{{ $formAction }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <main class="class-form-main">
            <section class="class-form-card">
                <div class="class-form-card__header"><span>01</span><div><h2>Thông tin lớp</h2><p>Tên gọi, giáo viên phụ trách và nội dung giới thiệu.</p></div></div>
                <div class="class-field-grid">
                    <div class="class-field"><label for="name">Tên lớp Yoga <span>*</span></label><div class="class-input-wrap"><i class="fa-solid fa-spa"></i><input type="text" id="name" name="name" value="{{ old('name', $class?->name) }}" maxlength="100" placeholder="Ví dụ: Yoga Balance buổi sáng" required autofocus></div>@error('name')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="teacher_id">Giáo viên hướng dẫn <span>*</span></label><div class="class-input-wrap"><i class="fa-solid fa-chalkboard-user"></i><select id="teacher_id" name="teacher_id" required><option value="">Chọn giáo viên</option>@foreach($teachers as $teacher)@php($teacherAvatar = $teacher->avatar && file_exists(public_path('storage/'.$teacher->avatar)) ? asset('storage/'.$teacher->avatar) : '')<option value="{{ $teacher->id }}" data-name="{{ $teacher->name }}" data-code="#{{ str_pad((string)$teacher->id,3,'0',STR_PAD_LEFT) }}" data-experience="{{ $teacher->exp_year }} năm kinh nghiệm" data-email="{{ $teacher->email }}" data-phone="{{ $teacher->phone }}" data-description="{{ $teacher->description }}" data-avatar="{{ $teacherAvatar }}" data-initial="{{ mb_strtoupper(mb_substr($teacher->name,0,1)) }}" {{ (string) old('teacher_id', $class?->teacher_id) === (string) $teacher->id ? 'selected' : '' }}>#{{ str_pad((string)$teacher->id,3,'0',STR_PAD_LEFT) }} · {{ $teacher->name }} · {{ $teacher->exp_year }} năm · {{ $teacher->email }}</option>@endforeach</select></div>@error('teacher_id')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div id="selectedTeacherProfile" class="class-teacher-selection class-field--full" hidden><div class="class-teacher-selection__avatar"><img id="selectedTeacherAvatar" src="" alt="" hidden><span id="selectedTeacherInitial">G</span></div><div class="class-teacher-selection__main"><div><small id="selectedTeacherCode">#000</small><strong id="selectedTeacherName">Giáo viên</strong><span id="selectedTeacherExperience">0 năm kinh nghiệm</span></div><p id="selectedTeacherDescription">Chưa có thông tin giới thiệu.</p></div><div class="class-teacher-selection__contact"><span><i class="fa-regular fa-envelope"></i><b id="selectedTeacherEmail">—</b></span><span><i class="fa-solid fa-phone"></i><b id="selectedTeacherPhone">—</b></span></div></div>
                    <div class="class-field class-field--full"><label for="description">Mô tả <small>Không bắt buộc</small></label><textarea id="description" name="description" maxlength="255" rows="4" placeholder="Mục tiêu, cấp độ hoặc điểm nổi bật của lớp...">{{ old('description', $class?->description) }}</textarea><div class="class-field-meta"><span>Nội dung này được hiển thị cho học viên</span><span id="classDescriptionCounter">0/255</span></div>@error('description')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                </div>
            </section>

            <section class="class-form-card">
                <div class="class-form-card__header"><span>02</span><div><h2>Lịch và địa điểm</h2><p>Xác định lịch lặp, khung giờ và thời gian diễn ra khóa học.</p></div></div>
                <div class="class-field-grid">
                    <div class="class-field"><label for="lich_hoc">Lịch học <span>*</span></label><div class="class-input-wrap"><i class="fa-regular fa-calendar"></i><input type="text" id="lich_hoc" name="lich_hoc" value="{{ old('lich_hoc', $class?->lich_hoc) }}" maxlength="50" placeholder="Ví dụ: Thứ 2, 4, 6" required></div>@error('lich_hoc')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="location">Địa điểm <span>*</span></label><div class="class-input-wrap"><i class="fa-solid fa-location-dot"></i><input type="text" id="location" name="location" value="{{ old('location', $class?->location) }}" maxlength="100" placeholder="Ví dụ: Phòng Lotus A1" required></div>@error('location')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="start_time">Giờ bắt đầu <span>*</span></label><input type="time" id="start_time" name="start_time" value="{{ old('start_time', $class?->start_time?->format('H:i')) }}" required>@error('start_time')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="end_time">Giờ kết thúc <span>*</span></label><input type="time" id="end_time" name="end_time" value="{{ old('end_time', $class?->end_time?->format('H:i')) }}" required>@error('end_time')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="start_date">Ngày bắt đầu <span>*</span></label><input type="date" id="start_date" name="start_date" value="{{ old('start_date', $class?->start_date?->format('Y-m-d')) }}" required>@error('start_date')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="end_date">Ngày kết thúc <span>*</span></label><input type="date" id="end_date" name="end_date" value="{{ old('end_date', $class?->end_date?->format('Y-m-d')) }}" required>@error('end_date')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                </div>
            </section>

            <section class="class-form-card">
                <div class="class-form-card__header"><span>03</span><div><h2>Sức chứa và học phí</h2><p>Thiết lập số chỗ nhận đăng ký và mức phí mỗi tháng.</p></div></div>
                <div class="class-field-grid">
                    <div class="class-field"><label for="quantity">Sức chứa <span>*</span></label><div class="class-input-wrap"><i class="fa-solid fa-user-group"></i><input type="number" id="quantity" name="quantity" value="{{ old('quantity', $class?->quantity) }}" min="{{ $minimumQuantity }}" max="50" placeholder="20" required></div><small class="class-field-help">{{ $isEdit && $minimumQuantity > 1 ? 'Không thể thấp hơn '.$minimumQuantity.' học viên đã xác nhận.' : 'Tối đa 50 học viên mỗi lớp.' }}</small>@error('quantity')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                    <div class="class-field"><label for="price">Học phí mỗi tháng <span>*</span></label><div class="class-input-wrap"><i class="fa-solid fa-coins"></i><input type="number" id="price" name="price" value="{{ old('price', $class?->price) }}" min="0" step="1000" placeholder="800000" required><span class="class-input-suffix">đ</span></div>@error('price')<small class="class-field-error">{{ $message }}</small>@enderror</div>
                </div>
            </section>
        </main>

        <aside class="class-form-preview">
            <div class="class-form-preview__heading"><span><i class="fa-regular fa-eye"></i></span><div><h2>Xem trước lớp học</h2><p>Cập nhật tức thời theo nội dung form.</p></div></div>
            <div class="class-preview-hero"><small>Lớp Yoga</small><h3 id="previewClassName">Tên lớp Yoga</h3><span id="previewTeacher">Chưa chọn giáo viên</span></div>
            <div class="class-preview-schedule"><small>Lịch học</small><strong id="previewSchedule">Chưa thiết lập</strong><span><i class="fa-regular fa-clock"></i> <b id="previewTime">--:-- – --:--</b></span></div>
            <dl class="class-preview-facts"><div><dt><i class="fa-solid fa-location-dot"></i> Địa điểm</dt><dd id="previewLocation">Chưa cập nhật</dd></div><div><dt><i class="fa-regular fa-calendar-check"></i> Thời gian khóa</dt><dd id="previewDates">Chưa cập nhật</dd></div><div><dt><i class="fa-solid fa-user-group"></i> Sức chứa</dt><dd id="previewQuantity">0 học viên</dd></div></dl>
            <div class="class-preview-price"><span>Học phí tháng<small>Giá hiển thị cho học viên</small></span><strong id="previewPrice">0đ</strong></div>
            <div class="class-form-notice"><i class="fa-solid fa-circle-info"></i><p><strong>Kiểm tra trùng lịch</strong><span>Hệ thống sẽ từ chối nếu giáo viên có lớp trùng ngày hoặc khung giờ.</span></p></div>
            <button type="submit" class="class-form-submit"><span>{{ $isEdit ? 'Lưu thay đổi' : 'Tạo lớp Yoga' }}</span><i class="fa-solid fa-arrow-right"></i></button>
            <a href="{{ $cancelRoute }}" class="class-form-cancel">Hủy và quay lại</a>
        </aside>
    </form>
</div>
