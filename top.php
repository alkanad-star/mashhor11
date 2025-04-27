<?php
// top.php
include 'header.php';
?>
<style>
  /* 1. Make all images fully responsive */
  img {
    max-width: 100%;
    height: auto !important;
  }

  /* 2. Remove underlines from every link */
  a {
    text-decoration: none !important;
    color: inherit;
  }

  /* 3. Ensure each service card fills its column and spaces its content */
  .service-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    overflow: hidden;
    box-shadow: 0 8px 16px 0 rgba(10,14,29,0.04), 0 8px 64px 0 rgba(10,14,29,0.08);
    border: none;
    white-space: inherit;
  }
</style>

<main>
  <div class="container py-1 py-lg-3" style="min-height:70vh">
    <div class="row p-2 p-lg-3">

      <!-- Followers Section -->
      <div class="col-12 my-2">
        <h3>
          <span style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>
          متابعين
        </h3>
        <div class="row p-2">

          <!-- Service 1 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/103" class="d-block">
                  <img src="images/tiktok.png"
                       alt="أرخص خدمة زيادة متابعين تيك توك (الارخص في العالم) + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/103" class="d-block font-lg-3 font-1">
                  أرخص خدمة زيادة متابعين تيك توك (الارخص في العالم) + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.03</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 1 -->

          <!-- Service 2 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/3" class="d-block">
                  <img src="images/tiktok.png"
                       alt="أفضل خدمة زيادة متابعين تيك توك ممتازة لتكبير الحساب + ضمان (الأكثر طلباً)"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/3" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة متابعين تيك توك ممتازة لتكبير الحساب + ضمان (الأكثر طلباً)
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.23</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 2 -->

          <!-- Service 3 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/96" class="d-block">
                  <img src="images/insta.png"
                       alt="خدمة زيادة متابعين انستقرام الأكثر طلباً + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/96" class="d-block font-lg-3 font-1">
                  خدمة زيادة متابعين انستقرام الأكثر طلباً + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.02</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 3 -->

          <!-- Service 4 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/81" class="d-block">
                  <img src="images/insta.png"
                       alt="أفضل خدمة زيادة متابعين انستقرام على الاطلاق (حقيقيين + متفاعلين + عرب 🇸🇦 + ضمان)"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/81" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة متابعين انستقرام على الاطلاق (حقيقيين + متفاعلين + عرب 🇸🇦 + ضمان)
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.11</span>
                    <span style="color:#626a87;">/ 20</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 4 -->

          <!-- Service 5 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/34" class="d-block">
                  <img src="images/telegram.png"
                       alt="أفضل خدمة زيادة مشتركين تيليجرام (قنوات وجروبات) + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/34" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة مشتركين تيليجرام (قنوات وجروبات) + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.14</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 5 -->

          <!-- Service 6 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/115" class="d-block">
                  <img src="images/tiktok.png"
                       alt="أرخص خدمة متابعين تيك توك في العالم - (🔴 يجب فتح بث مباشر قبل وأثناء تنفيذ الطلب)"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/115" class="d-block font-lg-3 font-1">
                  أرخص خدمة متابعين تيك توك في العالم - (🔴 يجب فتح بث مباشر قبل وأثناء تنفيذ الطلب)
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.00</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 6 -->

          <!-- Service 7 (YouTube Subscribers) -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/42" class="d-block">
                  <img src="images/youtube.png"
                       alt="أفضل خدمة مشتركين يوتيوب ممتازة لتحقيق شروط الربح من اليوتيوب + ضمان + زر التعويض مفعل"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/42" class="d-block font-lg-3 font-1">
                  أفضل خدمة مشتركين يوتيوب ممتازة لتحقيق شروط الربح من اليوتيوب + ضمان + زر التعويض مفعل
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$3.86</span>
                    <span style="color:#626a87;">/ 500</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 7 -->

          <!-- Service 8 (TikTok Arab) -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/114" class="d-block">
                  <img src="images/tiktok.png"
                       alt="أفضل خدمة متابعين تيك توك عرب حقيقيين ومتفاعلين 🇸🇦"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/114" class="d-block font-lg-3 font-1">
                  أفضل خدمة متابعين تيك توك عرب حقيقيين ومتفاعلين 🇸🇦
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$6.73</span>
                    <span style="color:#626a87;">/ 1000</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 8 -->

          <!-- Service 9 (Twitter) -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/13" class="d-block">
                  <img src="images/x.png"
                       alt="أرخص خدمة زيادة متابعين تويتر (الأرخص على الاطلاق) + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/13" class="d-block font-lg-3 font-1">
                  أرخص خدمة زيادة متابعين تويتر (الأرخص على الاطلاق) + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$2.34</span>
                    <span style="color:#626a87;">/ 150</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 9 -->

          <!-- Service 10 (Facebook Personal) -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/79" class="d-block">
                  <img src="images/face.png"
                       alt="أفضل خدمة زيادة متابعين فيسبوك للحسابات الشخصية + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/79" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة متابعين فيسبوك للحسابات الشخصية + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.27</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 10 -->

          <!-- Service 11 (Facebook Arab) -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/105" class="d-block">
                  <img src="images/face.png"
                       alt="أفضل خدمة زيادة متابعين عرب 100% 🇸🇦 ومتفاعلين لصفحات فيسبوك + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/105" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة متابعين عرب 100% 🇸🇦 ومتفاعلين لصفحات فيسبوك + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$1.62</span>
                    <span style="color:#626a87;">/ 500</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 11 -->

          <!-- Service 12 (Threads) -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/50" class="d-block">
                  <img src="images/threads.png"
                       alt="أفضل خدمة متابعين ثريدز حقيقيين نشطين + ضمان عدم نقصان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  متابعين
                </span>
              </div>
              <div class="p-3">
                <a href="/service/50" class="d-block font-lg-3 font-1">
                  أفضل خدمة متابعين ثريدز حقيقيين نشطين + ضمان عدم نقصان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.41</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 12 -->

        </div>
      </div>
      <!-- /Followers Section -->

      <!-- Interaction Section -->
      <div class="col-12 my-2">
        <h3>
          <span style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>
          تفاعل
        </h3>
        <div class="row p-2">

          <!-- Service 1 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/21" class="d-block">
                  <img src="images/tiktok.png"
                       alt="زيادة لايكات تيك توك (الأرخص على الاطلاق) + تساعد في حركة الاكسبلور"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تفاعل
                </span>
              </div>
              <div class="p-3">
                <a href="/service/21" class="d-block font-lg-3 font-1">
                  زيادة لايكات تيك توك (الأرخص على الاطلاق) + تساعد في حركة الاكسبلور
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.00</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 1 -->

          <!-- Service 2 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/46" class="d-block">
                  <img src="images/insta.png"
                       alt="زيادة لايكات انستقرام سريعة جداً (الأرخص على الاطلاق) + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تفاعل
                </span>
              </div>
              <div class="p-3">
                <a href="/service/46" class="d-block font-lg-3 font-1">
                  زيادة لايكات انستقرام سريعة جداً (الأرخص على الاطلاق) + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.01</span>
                    <span style="color:#626a87;">/ 20</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 2 -->

          <!-- Service 3 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/108" class="d-block">
                  <img src="images/telegram.png"
                       alt="أسرع خدمة تفاعلات ايجابية ❤️👍🥰 لمنشور التيليجرام + لا يوجد نقص"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تفاعل
                </span>
              </div>
              <div class="p-3">
                <a href="/service/108" class="d-block font-lg-3 font-1">
                  أسرع خدمة تفاعلات ايجابية ❤️👍🥰 لمنشور التيليجرام + لا يوجد نقص
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.00</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 3 -->

          <!-- Service 4 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/19" class="d-block">
                  <img src="images/youtube.png"
                       alt="أفضل خدمة زيادة لايكات يوتيوب سريعة جداً + زر التعويض مفعل + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تفاعل
                </span>
              </div>
              <div class="p-3">
                <a href="/service/19" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة لايكات يوتيوب سريعة جداً + زر التعويض مفعل + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.03</span>
                    <span style="color:#626a87;">/ 20</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 4 -->

          <!-- Service 5 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/7" class="d-block">
                  <img src="images/map.png"
                       alt="تقييم خرائط جوجل بدون تعليقات (5 نجوم ✨) من حسابات متنوعة + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تفاعل
                </span>
              </div>
              <div class="p-3">
                <a href="/service/7" class="d-block font-lg-3 font-1">
                  تقييم خرائط جوجل بدون تعليقات (5 نجوم ✨) من حسابات متنوعة + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$12.80</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 5 -->

        </div>
      </div>
      <!-- /Interaction Section -->

      <!-- Views Section -->
      <div class="col-12 my-2">
        <h3>
          <span style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>
          مشاهدات
        </h3>
        <div class="row p-2">

          <!-- Service 1 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/4" class="d-block">
                  <img src="images/tiktok.png"
                       alt="زيادة مشاهدات تيك توك (الأسرع والأفضل في العالم) سريعة جداً + ضمان + زر التعويض مفعل"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  مشاهدات
                </span>
              </div>
              <div class="p-3">
                <a href="/service/4" class="d-block font-lg-3 font-1">
                  زيادة مشاهدات تيك توك (الأسرع والأفضل في العالم) سريعة جداً + ضمان + زر التعويض مفعل
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.00</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 1 -->

          <!-- Service 2 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/45" class="d-block">
                  <img src="images/insta.png"
                       alt="زيادة مشاهدات انستقرام (فيديوهات) + ضمان + سريعة (الأرخص على الاطلاق)"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  مشاهدات
                </span>
              </div>
              <div class="p-3">
                <a href="/service/45" class="d-block font-lg-3 font-1">
                  زيادة مشاهدات انستقرام (فيديوهات) + ضمان + سريعة (الأرخص على الاطلاق)
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.23</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 2 -->

          <!-- Service 3 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/18" class="d-block">
                  <img src="images/youtube.png"
                       alt="أفضل خدمة زيادة ساعات مشاهدات يوتيوب + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  مشاهدات
                </span>
              </div>
              <div class="p-3">
                <a href="/service/18" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة ساعات مشاهدات يوتيوب + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.87</span>
                    <span style="color:#626a87;">/ 50</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 3 -->

          <!-- Service 4 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/10" class="d-block">
                  <img src="images/other-services.png"
                       alt="أفضل خدمة زيادة مشاهدات كواي حقيقيين 100%"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  مشاهدات
                </span>
              </div>
              <div class="p-3">
                <a href="/service/10" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة مشاهدات كواي حقيقيين 100%
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.06</span>
                    <span style="color:#626a87;">/ 100</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 4 -->

        </div>
      </div>
      <!-- /Views Section -->

      <!-- Live Streaming Section -->
      <div class="col-12 my-2">
        <h3>
          <span style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>
          بث مباشر
        </h3>
        <div class="row p-2">

          <!-- Service 1 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/94" class="d-block">
                  <img src="images/tiktok.png"
                       alt="أفضل خدمة زيادة مشاهدات تيك توك (بث مباشر) الأرخص + الأفضل + سريعة جداً"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  بث مباشر
                </span>
              </div>
              <div class="p-3">
                <a href="/service/94" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة مشاهدات تيك توك (بث مباشر) الأرخص + الأفضل + سريعة جداً
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.01</span>
                    <span style="color:#626a87;">/ 10</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 1 -->

        </div>
      </div>
      <!-- /Live Streaming Section -->

      <!-- Comments Section -->
      <div class="col-12 my-2">
        <h3>
          <span style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>
          تعليقات
        </h3>
        <div class="row p-2">

          <!-- Service 1 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/32" class="d-block">
                  <img src="images/insta.png"
                       alt="أفضل خدمة زيادة تعليقات انستقرام من حسابات عربية (تكتب التعليقات بنفسك)"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تعليقات
                </span>
              </div>
              <div class="p-3">
                <a href="/service/32" class="d-block font-lg-3 font-1">
                  أفضل خدمة زيادة تعليقات انستقرام من حسابات عربية (تكتب التعليقات بنفسك)
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$0.03</span>
                    <span style="color:#626a87;">/ 20</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 1 -->

          <!-- Service 2 -->
          <div class="col-6 col-sm-6 col-md-4 col-lg-3 px-2 px-lg-3 py-2">
            <div class="service-card btn rounded p-0">
              <div class="position-relative" style="padding-top:70%; background:url('/site_images/transparent.svg') no-repeat center/100%;">
                <a href="/service/44" class="d-block">
                  <img src="images/map.png"
                       alt="تقييم خرائط جوجل + تكتب التقييمات بنفسك (5 نجوم ✨) من حسابات عالمية متنوعة + ضمان"
                       loading="lazy">
                </a>
                <div class="fas fa-fire position-absolute"
                     style="top:7px; left:7px; z-index:44; color:#fff;"></div>
                <span style="position:absolute; right:10px; top:10px; background:#455A64; color:#fff; font-size:13px; padding:0 7px 7px; border-radius:5px;">
                  تعليقات
                </span>
              </div>
              <div class="p-3">
                <a href="/service/44" class="d-block font-lg-3 font-1">
                  تقييم خرائط جوجل + تكتب التقييمات بنفسك (5 نجوم ✨) من حسابات عالمية متنوعة + ضمان
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                    <span class="fas fa-star" style="color:#ff9800;"></span>
                  </div>
                  <div>
                    <span style="color:#ff9800;font-weight:bold;">$8.33</span>
                    <span style="color:#626a87;">/ 5</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /Service 2 -->

        </div>
      </div>
      <!-- /Comments Section -->

    </div>
  </div>
</main>

<?php
include 'footer.php';
?>
