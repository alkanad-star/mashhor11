<?php
// service.php
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
  /* Service card styling */
  .service-card {
    box-shadow: 0 8px 16px rgba(10,14,29,0.04), 0 8px 64px rgba(10,14,29,0.08);
    border: none;
    border-radius: 8px;
    overflow: hidden;
  }
  /* Feature box styling */
  .feature-box {
    box-shadow: 0 8px 16px rgba(10,14,29,0.02), 0 8px 64px rgba(10,14,29,0.03);
    border: 1px solid #eee;
    border-radius: 11px;
    height: 100%;
  }
  /* Rating stars */
  .rating-stars i {
    color: #ff9800;
    font-size: 13px;
    margin: 0 2px;
  }
  /* Comment styling */
  .comment {
    padding: 15px 13px 25px;
    border-bottom: 1px solid #f1f1f1;
  }
  .comment-badge {
    background: #ff9800;
    color: #fff;
    border-radius: 23px;
    font-size: 12px;
    padding: 0px 10px 4px;
  }
  /* Progress bar styling */
  .progress-bar {
    background: #f1f1f1;
    border-radius: 50px;
    height: 8px;
    position: relative;
    display: inline-block;
    width: 80%;
  }
  .progress-fill {
    background: #ff9800;
    height: 8px;
    border-radius: 50px;
  }
  /* Form elements */
  .form-control {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 15px;
  }
  .btn-order {
    background-color: #ff9800;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 600;
    transition: background-color 0.3s, transform 0.2s;
  }
  .btn-order:hover {
    background-color: #e68a00;
    transform: translateY(-2px);
  }
</style>

<main class="p-0 font-2">
  <section class="bg-light py-4">
    <div class="container">
      <div class="row g-4">
        <!-- Product Image -->
        <div class="col-lg-6">
          <div class="position-sticky" style="top: 105px">
            <figure class="rounded">
              <img src="/images/face.png" alt="أفضل خدمة زيادة متابعين + لايكات لصفحة فيسبوك" class="rounded">
            </figure>
          </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
          <div class="px-2 mt-2">
            <!-- Product Title -->
            <h2 class="display-5 mb-3">أفضل خدمة زيادة متابعين + لايكات لصفحة فيسبوك (أفضل قيمة مقابل السعر) + ضمان</h2>
            
            <!-- Product Rating -->
            <div class="mb-3">
              <span class="rating-stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </span>
              <span class="mx-2 d-inline-block">(66 مراجعة)</span>
            </div>
            
            <!-- Product Price -->
            <p class="mb-0">السعر لكل <span style="text-decoration:underline;">100</span></p>
            <p class="mb-4 fs-4" style="color:#ff9800"><span class="fw-bold">$0.43</span></p>
            
            <!-- Product Description -->
            <div class="mb-4 p-3 bg-white rounded">
              <p class="fs-5 text-justify">أفضل خدمة زيادة متابعين لصفحة فيسبوك (أعلى جودة ممكنة حسابات من جميع دول العالم) + ضمان</p>
            </div>
            
            <!-- Product Features -->
            <div class="row g-3 mb-4">
              <!-- Speed Feature -->
              <div class="col-6 col-lg-3">
                <div class="feature-box p-2 text-center">
                  <div class="d-flex flex-column align-items-center">
                    <div class="mb-2">
                      <span class="far fa-bolt fs-4" style="color:#ff9800"></span>
                    </div>
                    <div>
                      <div class="text-muted small">السرعة</div>
                      <div class="fw-bold">25K/اليوم</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Start Time Feature -->
              <div class="col-6 col-lg-3">
                <div class="feature-box p-2 text-center">
                  <div class="d-flex flex-column align-items-center">
                    <div class="mb-2">
                      <span class="far fa-stopwatch fs-4" style="color:#ff9800"></span>
                    </div>
                    <div>
                      <div class="text-muted small">وقت البدء</div>
                      <div class="fw-bold">10 دقائق</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Warranty Feature -->
              <div class="col-6 col-lg-3">
                <div class="feature-box p-2 text-center">
                  <div class="d-flex flex-column align-items-center">
                    <div class="mb-2">
                      <span class="far fa-shield-check fs-4" style="color:#ff9800"></span>
                    </div>
                    <div>
                      <div class="text-muted small">الضمان</div>
                      <div class="fw-bold">
                        <span class="fas fa-check-circle" style="color:#29a92e"></span> 30 يوم
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Quality Feature -->
              <div class="col-6 col-lg-3">
                <div class="feature-box p-2 text-center">
                  <div class="d-flex flex-column align-items-center">
                    <div class="mb-2">
                      <span class="far fa-check-double fs-4" style="color:#ff9800"></span>
                    </div>
                    <div>
                      <div class="text-muted small">الجودة</div>
                      <div class="progress-bar">
                        <div class="progress-fill" style="width:70%"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Additional Information -->
            <div class="mb-4 p-3 bg-white rounded text-center">
              <p class="mb-0"><span class="fas fa-check-circle" style="color:#ff9800"></span> زر الالغاء مفعل</p>
            </div>
            
            <div class="mb-4 p-3 bg-white rounded">
              <p class="mb-0">لا تضع اكثر من طلب لنفس الرابط بنفس الوقت الا لينتهي الطلب الاول حتى لا يحدث تداخل بالطلبات ولا يمكننا الغائها</p>
              <p class="mb-0">لا تنس وضع التقييم الخاص بك بعد انتهاء الخدمة للحصول على الهدية الخاصة بك</p>
            </div>
            
            <!-- Average Speed -->
            <div class="mb-4">
              <p class="mb-0">
                <span class="fas fa-bolt" style="color:#ff9800"></span> السرعة 10 ساعات 24 دقيقة
              </p>
              <div class="small text-muted">متوسط مدة التنفيذ لكل 100 زيادة بناءً على آخر 10 طلبات</div>
            </div>
            
            <!-- Order Form -->
            <form method="POST" action="#" id="order_form">
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-2">
                  <label class="form-label fw-bold">العدد المطلوب</label>
                  <span class="fs-5" style="color:#ff9800"><span class="fw-bold">$</span><span id="price-cost">0</span></span>
                </div>
                <input type="number" name="requested_number" id="requested_number" required min="100" max="1000000" value="100" class="form-control">
                <div class="small text-muted">الحد الأدنى لطلب الخدمة هو 100</div>
              </div>
              
              <div class="mb-4">
                <label class="form-label fw-bold">الرابط</label>
                <input type="text" name="user_link" required class="form-control" placeholder="ضع رابط الصفحة هنا">
              </div>
              
              <button type="submit" class="btn btn-order w-100">
                <i class="fal fa-shopping-cart me-2"></i> طلب الخدمة الآن
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Reviews Section -->
  <section class="py-4">
    <div class="container">
      <!-- Reviews Header -->
      <h3 class="mb-4">التقييمات والمراجعات</h3>
      
      <!-- Reviews List -->
      <div class="row">
        <div class="col-12">
          <!-- Review 1 -->
          <div class="comment">
            <div class="d-flex justify-content-between">
              <div class="d-flex align-items-start">
                <img src="/images/avatars/avatar-1.png" alt="حماده اللواء" class="rounded-circle" width="30" height="30">
                <div class="ms-2">
                  <div class="fw-bold">حماده اللواء</div>
                  <div class="rating-stars small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <span class="comment-badge"><i class="fal fa-check"></i><i class="fal fa-check"></i> قام بالشراء</span>
                </div>
              </div>
              <div class="text-muted small">منذ 3 أسابيع</div>
            </div>
            <div class="mt-2 p-2">ممتاز وسرعة التسليم</div>
          </div>
          
          <!-- Review 2 -->
          <div class="comment">
            <div class="d-flex justify-content-between">
              <div class="d-flex align-items-start">
                <img src="/images/avatars/avatar-2.png" alt="Ali Ghanem" class="rounded-circle" width="30" height="30">
                <div class="ms-2">
                  <div class="fw-bold">Ali Ghanem</div>
                  <div class="rating-stars small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <span class="comment-badge"><i class="fal fa-check"></i><i class="fal fa-check"></i> قام بالشراء</span>
                </div>
              </div>
              <div class="text-muted small">منذ 3 أسابيع</div>
            </div>
            <div class="mt-2 p-2">روعة</div>
          </div>
          
          <!-- Review 3 -->
          <div class="comment">
            <div class="d-flex justify-content-between">
              <div class="d-flex align-items-start">
                <img src="/images/avatars/avatar-3.png" alt="Service Howara" class="rounded-circle" width="30" height="30">
                <div class="ms-2">
                  <div class="fw-bold">Service Howara</div>
                  <div class="rating-stars small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <span class="comment-badge"><i class="fal fa-check"></i><i class="fal fa-check"></i> قام بالشراء</span>
                </div>
              </div>
              <div class="text-muted small">منذ 4 أسابيع</div>
            </div>
            <div class="mt-2 p-2">Très serieus</div>
          </div>
          
          <!-- Pagination -->
          <div class="d-flex justify-content-center mt-4">
            <nav>
              <ul class="pagination">
                <li class="page-item disabled"><a class="page-link" href="#">&laquo; السابق</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">4</a></li>
                <li class="page-item"><a class="page-link" href="#">التالي &raquo;</a></li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Related Products Section -->
  <section class="py-4 bg-light">
    <div class="container">
      <h3 class="mb-4">اشترى المستخدمون أيضاً</h3>
      
      <div class="row g-3">
        <!-- Related Product 1 -->
        <div class="col-6 col-md-4 col-lg-3">
          <div class="service-card btn rounded p-0 h-100">
            <div class="position-relative" style="padding-top:70%;background:url('/site_images/transparent.svg') center/100% no-repeat;">
              <div class="position-absolute" style="left:0;top:7px;color:#fff;font-size:15px;width:30px;display:flex;justify-content:center;z-index:44;" title="الأكثر مبيعاً">
                <i class="fas fa-fire"></i>
              </div>
              <a href="/service/32-زيادة-تعليقات-انستقرام-مخصصة" class="d-block">
                <img src="/images/insta.png"
                     alt="أفضل خدمة زيادة تعليقات انستقرام من حسابات عربية (تكتب التعليقات بنفسك)"
                     loading="lazy">
              </a>
              <span class="badge bg-secondary position-absolute top-0 end-0 m-2">تعليقات</span>
            </div>
            <div class="p-3">
              <a href="/service/32-زيادة-تعليقات-انستقرام-مخصصة" class="h6 d-block mb-2">
                أفضل خدمة زيادة تعليقات انستقرام من حسابات عربية (تكتب التعليقات بنفسك)
              </a>
              <div class="d-flex justify-content-between align-items-center">
                <div class="rating-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
                <div>
                  <strong style="color:#ff9800">$0.03</strong> / 20
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Related Product 2 -->
        <div class="col-6 col-md-4 col-lg-3">
          <div class="service-card btn rounded p-0 h-100">
            <div class="position-relative" style="padding-top:70%;background:url('/site_images/transparent.svg') center/100% no-repeat;">
              <div class="position-absolute" style="left:0;top:7px;color:#fff;font-size:15px;width:30px;display:flex;justify-content:center;z-index:44;" title="الأكثر مبيعاً">
                <i class="fas fa-fire"></i>
              </div>
              <a href="/service/42-أفضل-خدمة-زيادة-مشتركين-يوتيوب" class="d-block">
                <img src="/images/youtube.png"
                     alt="أفضل خدمة مشتركين يوتيوب ممتازة لتحقيق شروط الربح من اليوتيوب + ضمان + زر التعويض مفعل"
                     loading="lazy">
              </a>
              <span class="badge bg-secondary position-absolute top-0 end-0 m-2">متابعين</span>
            </div>
            <div class="p-3">
              <a href="/service/42-أفضل-خدمة-زيادة-مشتركين-يوتيوب" class="h6 d-block mb-2">
                أفضل خدمة مشتركين يوتيوب ممتازة لتحقيق شروط الربح من اليوتيوب + ضمان + زر التعويض مفعل
              </a>
              <div class="d-flex justify-content-between align-items-center">
                <div class="rating-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
                <div>
                  <strong style="color:#ff9800">$3.86</strong> / 500
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Related Product 3 -->
        <div class="col-6 col-md-4 col-lg-3">
          <div class="service-card btn rounded p-0 h-100">
            <div class="position-relative" style="padding-top:70%;background:url('/site_images/transparent.svg') center/100% no-repeat;">
              <div class="position-absolute" style="left:0;top:7px;color:#fff;font-size:15px;width:30px;display:flex;justify-content:center;z-index:44;" title="الأكثر مبيعاً">
                <i class="fas fa-fire"></i>
              </div>
              <a href="/service/46-زيادة-لايكات-انستقرام-سريعة-جداً" class="d-block">
                <img src="/images/insta.png"
                     alt="زيادة لايكات انستقرام سريعة جداً (الأرخص على الاطلاق) + ضمان"
                     loading="lazy">
              </a>
              <span class="badge bg-secondary position-absolute top-0 end-0 m-2">تفاعل</span>
            </div>
            <div class="p-3">
              <a href="/service/46-زيادة-لايكات-انستقرام-سريعة-جداً" class="h6 d-block mb-2">
                زيادة لايكات انستقرام سريعة جداً (الأرخص على الاطلاق) + ضمان
              </a>
              <div class="d-flex justify-content-between align-items-center">
                <div class="rating-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
                <div>
                  <strong style="color:#ff9800">$0.01</strong> / 20
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Related Product 4 -->
        <div class="col-6 col-md-4 col-lg-3">
          <div class="service-card btn rounded p-0 h-100">
            <div class="position-relative" style="padding-top:70%;background:url('/site_images/transparent.svg') center/100% no-repeat;">
              <div class="position-absolute" style="left:0;top:7px;color:#fff;font-size:15px;width:30px;display:flex;justify-content:center;z-index:44;" title="الأكثر مبيعاً">
                <i class="fas fa-fire"></i>
              </div>
              <a href="/service/103-أرخص-خدمة-زيادة-متابعين-تيك-توك-في-العالم" class="d-block">
                <img src="/images/tiktok.png"
                     alt="أرخص خدمة زيادة متابعين تيك توك (الارخص في العالم) + ضمان"
                     loading="lazy">
              </a>
              <span class="badge bg-secondary position-absolute top-0 end-0 m-2">متابعين</span>
            </div>
            <div class="p-3">
              <a href="/service/103-أرخص-خدمة-زيادة-متابعين-تيك-توك-في-العالم" class="h6 d-block mb-2">
                أرخص خدمة زيادة متابعين تيك توك (الارخص في العالم) + ضمان
              </a>
              <div class="d-flex justify-content-between align-items-center">
                <div class="rating-stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </div>
                <div>
                  <strong style="color:#ff9800">$0.03</strong> / 10
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- JavaScript for price calculation -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const numberInput = document.getElementById('requested_number');
    const priceDisplay = document.getElementById('price-cost');
    const pricePerItem = 0.43; // Price per 100 items
    
    function updatePrice() {
      const quantity = parseInt(numberInput.value);
      const price = (quantity / 100) * pricePerItem;
      priceDisplay.textContent = price.toFixed(2);
    }
    
    numberInput.addEventListener('input', updatePrice);
    updatePrice(); // Initial calculation
  });
</script>

<?php
include 'footer.php';
?>