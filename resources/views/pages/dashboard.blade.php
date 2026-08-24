@extends('layouts.app')

@section('title', 'Sống khỏe mỗi ngày - VITA Yoga Center')

@section('content')
<div class="health-dashboard">
    <section class="health-hero">
        <div class="health-hero__content">
            <span class="health-eyebrow">
                <i class="fa-solid fa-leaf" aria-hidden="true"></i>
                Không gian luyện tập dành cho bạn
            </span>
            <h1>Khỏe hơn mỗi ngày,<br><span>vững vàng từ bên trong.</span></h1>
            <p>
                Chọn lớp học phù hợp với thể trạng, lịch trình và mục tiêu của bạn.
                Chúng tôi đồng hành từ buổi tập đầu tiên đến khi vận động trở thành thói quen.
            </p>
            <div class="health-hero__actions">
                <a href="{{ route('classes') }}" class="health-btn health-btn--primary">
                    Khám phá lớp học <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
                <a href="{{ route('teachers') }}" class="health-btn health-btn--secondary">
                    Gặp gỡ giáo viên Yoga
                </a>
            </div>
            <div class="health-trust">
                <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Lộ trình phù hợp</span>
                <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Lịch học linh hoạt</span>
                <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Theo sát tiến độ</span>
            </div>
        </div>

        <div class="wellness-card" aria-label="Thông điệp sức khỏe hôm nay">
            <div class="wellness-card__orb wellness-card__orb--one"></div>
            <div class="wellness-card__orb wellness-card__orb--two"></div>
            <div class="wellness-card__icon">
                <i class="fa-solid fa-heart-pulse" aria-hidden="true"></i>
            </div>
            <p class="wellness-card__label">Nhịp sống lành mạnh</p>
            <h2>Bắt đầu từ một buổi tập.</h2>
            <p>Thay đổi nhỏ, được lặp lại đều đặn, sẽ tạo nên một cơ thể khỏe mạnh và tinh thần cân bằng.</p>
            <div class="wellness-card__progress">
                <div class="wellness-card__progress-head">
                    <span>Mục tiêu hôm nay</span>
                    <strong>60 phút vận động</strong>
                </div>
                <div class="wellness-card__track"><span></span></div>
            </div>
        </div>
    </section>

    <section class="health-stats" aria-label="Thống kê trung tâm">
        <div class="health-stat"><strong>{{ number_format($classesCount) }}</strong><span>Lớp học đang có</span></div>
        <div class="health-stat"><strong>{{ number_format($teachersCount) }}</strong><span>Giáo viên Yoga</span></div>
        <div class="health-stat"><strong>{{ number_format($membersCount) }}</strong><span>Thành viên đồng hành</span></div>
        <div class="health-stat"><strong>{{ number_format($registrationsCount) }}</strong><span>Lượt đăng ký</span></div>
    </section>

    <section class="health-section">
        <div class="health-section__heading">
            <div>
                <span class="health-eyebrow">Luyện tập theo cách của bạn</span>
                <h2>Một nơi cho mọi mục tiêu sức khỏe</h2>
            </div>
            <p>Từ thư giãn, cải thiện độ dẻo dai đến xây dựng sức mạnh, bạn luôn có một điểm bắt đầu phù hợp.</p>
        </div>

        <div class="health-features">
            <article class="health-feature">
                <div class="health-feature__icon health-feature__icon--mint"><i class="fa-solid fa-spa" aria-hidden="true"></i></div>
                <span>01</span><h3>Yoga cân bằng</h3>
                <p>Cải thiện độ dẻo dai, hơi thở và sự tập trung qua lộ trình từ cơ bản đến nâng cao.</p>
            </article>
            <article class="health-feature">
                <div class="health-feature__icon health-feature__icon--lime"><i class="fa-solid fa-person" aria-hidden="true"></i></div>
                <span>02</span><h3>Power Yoga</h3>
                <p>Chuỗi động tác giàu năng lượng giúp cải thiện sức mạnh, sức bền và khả năng kiểm soát cơ thể.</p>
            </article>
            <article class="health-feature">
                <div class="health-feature__icon health-feature__icon--sand"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></div>
                <span>03</span><h3>Lịch học linh hoạt</h3>
                <p>Nhiều khung giờ trong ngày để việc chăm sóc sức khỏe dễ dàng hòa vào nhịp sống của bạn.</p>
            </article>
            <article class="health-feature">
                <div class="health-feature__icon health-feature__icon--blue"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></div>
                <span>04</span><h3>Đồng hành chuyên môn</h3>
                <p>Giáo viên theo sát kỹ thuật và hỗ trợ bạn duy trì động lực trong từng buổi tập.</p>
            </article>
        </div>
    </section>

    <section class="health-cta">
        <div>
            <span class="health-eyebrow health-eyebrow--light">Sẵn sàng bắt đầu?</span>
            <h2>Chọn một lớp học phù hợp ngay hôm nay.</h2>
            <p>Một quyết định nhỏ hôm nay có thể tạo nên phiên bản khỏe mạnh hơn của bạn ngày mai.</p>
        </div>
        <a href="{{ route('register') }}" class="health-btn health-btn--light">
            Đăng ký lớp học <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </section>
</div>
@endsection
