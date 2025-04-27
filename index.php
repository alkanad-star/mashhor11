<?php
// index.php
include 'header.php';
?>

<main>
    <!-- Free Services Banner -->
    <div class="container mt-4">
    <div class="promo-banner">
        <span>اشحن رصيدك الآن واحصل على 10% هدية إضافية فورًا – العرض لفترة محدودة!</span>
        <a href="/balance" class="btn btn-promo">
            <i class="fas fa-gift"></i>
            اشحن الآن
        </a>
    </div>
</div>

    
    <!-- Hero Section -->
    <section class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2">
                    <div class="hero-image">
                        <div class="hero-svg-animation">
                            <img src="images/anim.webp" alt="Social Media Growth" class="img-fluid animate-float">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <h1 class="hero-title">أهلاً بك في متجر مشهور</h1>
                    <p class="hero-description">
                        نقدم لك أسهل وأسرع خدمة لزيادة المتابعين والتفاعل على مواقع التواصل الاجتماعي في الوطن العربي.
                        <strong>متجر مشهور هو أفضل وأرخص موقع لزيادة المتابعين</strong>
                    </p>
                   <a href="/top" class="btn btn-cta"> <i class="fas fa-shopping-cart me-2"></i> أشهر الخدمات </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="images/feature-1.png" alt="متجر متكامل">
                        </div>
                        <h3>متجر متكامل</h3>
                        <p>جميع الخدمات الاحترافية التي تحتاجها لتعزيز نشاطك التجاري بين كل منافسيك</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="images/feature-2.png" alt="جربه مجاناً">
                        </div>
                        <h3>جربه مجاناً</h3>
                        <p>يمكنك استخدام الرصيد المجاني في تجربة كافة الخدمات المتاحة وقم بشحن الرصيد فقط عندما تحتاج</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="images/feature-3.png" alt="وفر المزيد">
                        </div>
                        <h3>وفر المزيد</h3>
                        <p>حد أدنى للشحن لا يتجاوز دولار واحد . واحصل على العديد من الهدايا عند الشحن</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/top" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/top-services.jpg')"></div>
                            <div class="category-name">الأكثر مبيعاً</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/face" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/face.png')"></div>
                            <div class="category-name">فيسبوك</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/insta" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/insta.png')"></div>
                            <div class="category-name">انستجرام</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/tiktok" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/tiktok.png')"></div>
                            <div class="category-name">تيك توك</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/youtube" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/youtube.png')"></div>
                            <div class="category-name">يوتيوب</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/snap" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/snap.png')"></div>
                            <div class="category-name">سناب شات</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/x" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/x.png')"></div>
                            <div class="category-name">تويتر</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/map" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/map.png')"></div>
                            <div class="category-name">جوجل ماب</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/other" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/other-services.png')"></div>
                            <div class="category-name">أخرى</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-6 col-lg-4 mb-4">
                    <a href="/services/threads" class="category-link">
                        <div class="category-card">
                            <div class="category-image" style="background-image: url('images/threads.png')"></div>
                            <div class="category-name">ثريدز</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section class="services-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">أكثر الخدمات طلباً</h2>
            <div class="row">
                <?php
                $services = [
                    [
                        'id' => 1,
                        'title' => 'زيادة مشاهدات تيك توك (الأسرع والأفضل في العالم) سريعة جداً + ضمان',
                        'category' => 'مشاهدات',
                        'image' => 'images/tiktok.png',
                        'price' => '0.00',
                        'unit' => 100,
                        'rating' => 5,
                        'link' => '/service/1'
                    ],
                    [
                        'id' => 2,
                        'title' => 'زيادة لايكات تيك توك (الأرخص على الاطلاق) + تساعد في حركة الاكسبلور',
                        'category' => 'تفاعل',
                        'image' => 'images/tiktok.png',
                        'price' => '0.00',
                        'unit' => 10,
                        'rating' => 5,
                        'link' => '/service/2'
                    ],
                    [
                        'id' => 3,
                        'title' => 'أرخص خدمة زيادة متابعين تيك توك (الارخص في العالم) + ضمان',
                        'category' => 'متابعين',
                        'image' => 'images/tiktok.png',
                        'price' => '0.03',
                        'unit' => 10,
                        'rating' => 5,
                        'link' => '/service/3'
                    ],
                    [
                        'id' => 4,
                        'title' => 'زيادة لايكات انستقرام سريعة جداً (الأرخص على الاطلاق) + ضمان',
                        'category' => 'تفاعل',
                        'image' => 'images/insta.png',
                        'price' => '0.01',
                        'unit' => 20,
                        'rating' => 5,
                        'link' => '/service/4'
                    ],
                    [
                        'id' => 5,
                        'title' => 'أفضل خدمة زيادة متابعين تيك توك ممتازة لتكبير الحساب + ضمان (الأكثر طلباً)',
                        'category' => 'متابعين',
                        'image' => 'images/tiktok.png',
                        'price' => '0.23',
                        'unit' => 100,
                        'rating' => 5,
                        'link' => '/service/5'
                    ],
                    [
                        'id' => 6,
                        'title' => 'زيادة مشاهدات انستقرام (فيديوهات) + ضمان + سريعة (الأرخص على الاطلاق)',
                        'category' => 'مشاهدات',
                        'image' => 'images/insta.png',
                        'price' => '0.00',
                        'unit' => 100,
                        'rating' => 5,
                        'link' => '/service/6'
                    ],
                    [
                        'id' => 7,
                        'title' => 'أفضل خدمة زيادة مشاهدات تيك توك (بث مباشر) الأرخص + الأفضل + سريعة جداً',
                        'category' => 'بث مباشر',
                        'image' => 'images/tiktok.png',
                        'price' => '0.01',
                        'unit' => 10,
                        'rating' => 5,
                        'link' => '/service/7'
                    ],
                    [
                        'id' => 8,
                        'title' => 'خدمة زيادة متابعين انستقرام الأكثر طلباً + ضمان',
                        'category' => 'متابعين',
                        'image' => 'images/insta.png',
                        'price' => '0.02',
                        'unit' => 10,
                        'rating' => 5,
                        'link' => '/service/8'
                    ],
                    [
                        'id' => 9,
                        'title' => 'أسرع خدمة تفاعلات ايجابية ❤️👍🥰 لمنشور التيليجرام + لا يوجد نقص',
                        'category' => 'تفاعل',
                        'image' => 'images/telegram.png',
                        'price' => '0.00',
                        'unit' => 10,
                        'rating' => 5,
                        'link' => '/service/9'
                    ],
                    [
                        'id' => 10,
                        'title' => 'أفضل خدمة زيادة لايكات يوتيوب سريعة جداً + زر التعويض مفعل + ضمان',
                        'category' => 'تفاعل',
                        'image' => 'images/youtube.png',
                        'price' => '0.03',
                        'unit' => 20,
                        'rating' => 5,
                        'link' => '/service/10'
                    ]
                ];
                
                foreach ($services as $service): ?>
                <div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="service-card">
                        <div class="service-image">
                            <a href="<?php echo $service['link']; ?>">
                                <img src="<?php echo $service['image']; ?>" alt="<?php echo $service['title']; ?>">
                            </a>
                            <span class="hot-badge"><i class="fas fa-fire"></i></span>
                            <span class="category-badge"><?php echo $service['category']; ?></span>
                        </div>
                        <div class="service-info">
                            <h3 class="service-title">
                                <a href="<?php echo $service['link']; ?>"><?php echo $service['title']; ?></a>
                            </h3>
                            <div class="service-rating">
                                <?php for($i = 0; $i < $service['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="service-price">
                                <span class="price">$<?php echo $service['price']; ?></span>
                                <span class="unit">/ <?php echo $service['unit']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section class="faq-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-3">الأسئلة الشائعة</h2>
            <p class="section-description text-center mb-5">نرد على الاستفسارات الخاصة بكم في صورة سؤال وجواب.</p>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            ما هو متجر مشهور وكيف يفيدني؟
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            متجر مشهور هو متجر متخصص يقدم لك خدمة زيادة المتابعين والتفاعل على جميع حساباتك على مواقع التواصل الاجتماعي ( انستجرام - فيسبوك - تويتر وغيرها ). كما يمكنك أيضاً زيادة التفاعل والمتابعين على منصات مشاهدات الفيديو ( يوتيوب - تيك توك - كواي وغيرها ).
                        </div>
                    </div>
                </div>
                
                <!--<div class="accordion-item">-->
                <!--    <h3 class="accordion-header">-->
                <!--        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">-->
                <!--            أريد أن أقوم بتجربة هذه الخدمات أولاً قبل شرائها ماذا أفعل؟-->
                <!--        </button>-->
                <!--    </h3>-->
                <!--    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">-->
                <!--        <div class="accordion-body">-->
                <!--            بكل تأكيد . عند التسجيل تحصل على رصيد مجاني ابتدائي . يمكنك تجربة أحد الخدمات الموجودة بهذا الرصيد المبدئي دون شحن أي رصيد-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            هل الخدمات المعروضة على متجر مشهور آمنة؟
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            بكل تأكيد جميع الخدمات الموجودة على المتجر آمنة تماماً ولا تؤثر نهائياً على حسابك بأي شكل
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            أحتاج شحن الرصيد في متجر مشهور كيف ذلك وما هي وسائل الشحن المتاحة؟
                        </button>
                    </h3>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            يمكنك بكل بساطة شحن حسابك في متجر مشهور عبر البطاقات الائتمانية فيزا وماستر كارد (Visa - MasterCard)، أو عبر الدولار الرقمي USDT، أو من خلال Binance Pay باستخدام USDT، أو عن طريق تحويل بنكي. كما يمكنك الشحن عبر بنك الكريمي (اليمن)، أو من خلال حوالة محلية عبر النجم أو الامتياز (اليمن)، أو باستخدام المحافظ المحلية في اليمن مثل (جيب - ون كاش).
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                            أحتاج زيادة المتابعين ولكن على فترات
                        </button>
                    </h3>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            يقدم لك متجر مشهور خاصية الزيادة على فترات في بعض الخدمات . يمكنك بكل بساطة تفعل خيار الزيادة على فترات وبالتالي يمكنك تحديد المدى الزمني لتنفيذ الخدمة وكذلك العدد الذي تحتاج زيادته
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                            ما هو الحد الأدنى للشحن في متجر مشهور؟
                        </button>
                    </h3>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            الحد الأدنى للشحن هو 2 دولار فقط !
                        </div>
                    </div>
                </div>
                
                <!--<div class="accordion-item">-->
                <!--    <h3 class="accordion-header">-->
                <!--        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">-->
                <!--            أحتاج شحن رصيدي في متجر مشهور بوسائل دفع أخرى هل هذا متاح؟-->
                <!--        </button>-->
                <!--    </h3>-->
                <!--    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">-->
                <!--        <div class="accordion-body">-->
                <!--            بكل تأكيد يمكنك شحن الرصيد عبر فودافون كاش وجميع المحافظ الاكترونية في مصر - وكذلك يمكنك شحن الرصيد عبر PayPal والمزيد من بوابات الدفع . فقط تواصل معنا بالوسيلة التي تحتاج الشحن من خلالنا لنقوم مباشرةً بارسال تعليمات الشحن .-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                
                <!--<div class="accordion-item">-->
                <!--    <h3 class="accordion-header">-->
                <!--        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">-->
                <!--            هل يمكنني رد مبلغ قمت بشحنه ولم أقم باستخدامه؟-->
                <!--        </button>-->
                <!--    </h3>-->
                <!--    <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">-->
                <!--        <div class="accordion-body">-->
                <!--            بكل تأكيد يمكن رد أي مبلغ لم يتم استخدامه . أو حتى جزء من المبلغ-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                            لدي استفسارات اخرى وأحتاج جواب لها ماذا أفعل؟
                        </button>
                    </h3>
                    <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            نتشرف بتلقي كل استفساراتك عبر الواتس اب أو تلجرام
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    /* Hero Section */
    .promo-banner {
        background: #28a745;
        color: #ffffff;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
    }
    
    .btn-promo {
        background: #ffffff;
        color: #28a745;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-promo:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }
    
    .btn-promo i {
        margin-left: 8px;
    }
    
    .hero-section {
        background: #f8f9fa;
        padding: 80px 0;
    }
    
    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--text-color);
    }
    
    .hero-description {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #666;
        margin-bottom: 30px;
    }
    
    .btn-cta {
        background: var(--primary-color);
        color: #ffffff;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
    }
    
    .btn-cta:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(244, 67, 54, 0.3);
    }
    
    .hero-image {
        text-align: center;
    }
    
    .hero-svg-animation {
        display: inline-block;
        position: relative;
        animation: float 3s ease-in-out infinite;
    }
    
    /* Features Section */
    .features-section {
        background: #ffffff;
        padding: 80px 0;
    }
    
    .feature-card {
        text-align: center;
        padding: 30px;
        border-radius: 15px;
        background: #ffffff;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .feature-icon {
        margin-bottom: 20px;
    }
    
    .feature-icon img {
        width: 200px;
        height: 200px;
        object-fit: contain;
    }
    
    .feature-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: var(--text-color);
    }
    
    .feature-card p {
        color: #666;
        line-height: 1.6;
    }
    
    /* Categories Section */
    .categories-section {
        background: #f8f9fa;
        padding: 80px 0;
    }
    
    .category-link {
        text-decoration: none;
        display: block;
    }
    
    .category-card {
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        height: 200px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .category-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: all 0.3s ease;
    }
    
    .category-card:hover .category-image {
        transform: scale(1.1);
    }
    
    .category-image::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        transition: all 0.3s ease;
    }
    
    .category-card:hover .category-image::after {
        background: rgba(0,0,0,0.3);
    }
    
    .category-name {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 700;
        z-index: 1;
        text-align: center;
        width: 100%;
    }
    
    /* Services Section */
    .services-section {
        background: #ffffff;
        padding: 80px 0;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-color);
    }
    
    .service-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .service-image {
        position: relative;
        padding-top: 70%;
        overflow: hidden;
    }
    
    .service-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s ease;
    }
    
    .service-card:hover .service-image img {
        transform: scale(1.1);
    }
    
    .hot-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #ff3e3e;
        color: #ffffff;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .category-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.7);
        color: #ffffff;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    
    .service-info {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .service-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }
    
    .service-title a {
        color: var(--text-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .service-title a:hover {
        color: var(--primary-color);
    }
    
    .service-rating {
        margin-bottom: 10px;
    }
    
    .service-rating i {
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    .service-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-color);
        border-top: 1px solid #eee;
        padding-top: 10px;
        margin-top: 10px;
    }
    
    .service-price .unit {
        font-size: 0.9rem;
        color: #666;
        font-weight: 400;
    }
    
    /* FAQ Section */
    .faq-section {
        background: #f8f9fa;
        padding: 80px 0;
    }
    
    .section-description {
        font-size: 1.1rem;
        color: #666;
    }
    
    .accordion-item {
        border: none;
        margin-bottom: 15px;
        border-radius: 10px !important;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .accordion-button {
        background: #ffffff;
        color: var(--text-color);
        font-weight: 600;
        padding: 20px 25px;
        border: none;
        font-size: 1.1rem;
    }
    
    .accordion-button:not(.collapsed) {
        background: var(--primary-color);
        color: #ffffff;
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border: none;
    }
    
    .accordion-button::after {
        margin-right: 10px;
        margin-left: 0;
    }
    
    .accordion-body {
        padding: 20px 25px;
        color: #666;
        line-height: 1.8;
    }
    
    @keyframes float {
        0% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
        100% {
            transform: translateY(0px);
        }
    }
    
    @media (max-width: 991px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-description {
            font-size: 1rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
    }
    
    @media (max-width: 767px) {
        .promo-banner {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .hero-title {
            font-size: 1.8rem;
        }
        
        .category-name {
            font-size: 1.2rem;
        }
        
        .service-title {
            font-size: 0.9rem;
        }
    }
</style>

<?php
include 'footer.php';
?>