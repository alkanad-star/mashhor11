<?php
// threads.php
include 'header.php';
?>
<style>
  /* make images fully responsive */
  img {
    max-width: 100%;
    height: auto !important;
  }
  /* remove underlines from all links */
  a {
    text-decoration: none !important;
    color: inherit;
  }
  /* unify service‑card styling */
  .service-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    overflow: hidden;
    box-shadow: 0 8px 16px rgba(10,14,29,0.04), 0 8px 64px rgba(10,14,29,0.08);
    border: none;
    white-space: inherit;
  }
</style>

<main class="p-0 font-2">
  <div class="container py-1 py-lg-3" style="min-height:70vh">
    <!-- Followers Section -->
    <section class="mb-5">
      <h3><span class="me-2" style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>متابعين ثريدز</h3>
      <div class="row g-3">
        <!-- Service 50 -->
        <div class="col-6 col-md-4 col-lg-3">
          <div class="service-card btn rounded p-0">
            <div class="position-relative" style="padding-top:70%;background:url('/site_images/transparent.svg') center/100% no-repeat;">
              <div class="position-absolute" style="left:0;top:7px;color:#fff;font-size:15px;width:30px;display:flex;justify-content:center;z-index:44;" title="الأكثر مبيعاً">
                <i class="fas fa-fire"></i>
              </div>
              <a href="/service/50-أفضل-خدمة-متابعين-ثريدز-حقيقيين-نشطين" class="d-block">
                <img src="../images/threads.png"
                     alt="أفضل خدمة متابعين ثريدز حقيقيين نشطين + ضمان عدم نقصان"
                     loading="lazy">
              </a>
              <span class="badge bg-secondary position-absolute top-0 end-0 m-2">متابعين</span>
            </div>
            <div class="p-3">
              <a href="/service/50-أفضل-خدمة-متابعين-ثريدز-حقيقيين-نشطين" class="h6 d-block mb-2">
                أفضل خدمة متابعين ثريدز حقيقيين نشطين + ضمان عدم نقصان
              </a>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                </div>
                <div>
                  <strong class="text-success">$0.41</strong> / 100
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Interaction Section -->
    <section class="mb-5">
      <h3><span class="me-2" style="background:#ff9800;width:12px;height:18px;display:inline-block;"></span>تفاعل ثريدز</h3>
      <div class="row g-3">
        <!-- Service 51 -->
        <div class="col-6 col-md-4 col-lg-3">
          <div class="service-card btn rounded p-0">
            <div class="position-relative" style="padding-top:70%;background:url('/site_images/transparent.svg') center/100% no-repeat;">
              <a href="/service/51-أفضل-خدمة-زيادة-لايكات-ثريدز" class="d-block">
                <img src="../images/threads.png"
                     alt="زيادة لايكات ثريدز سريعة + ضمان عدم نقصان"
                     loading="lazy">
              </a>
              <span class="badge bg-secondary position-absolute top-0 end-0 m-2">تفاعل</span>
            </div>
            <div class="p-3">
              <a href="/service/51-أفضل-خدمة-زيادة-لايكات-ثريدز" class="h6 d-block mb-2">
                زيادة لايكات ثريدز سريعة + ضمان عدم نقصان
              </a>
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                </div>
                <div>
                  <strong class="text-success">$0.01</strong> / 10
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>

<?php
include 'footer.php';
?>