<!-- Services Management -->
<div class="services-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">إدارة الخدمات</h1>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                <i class="fas fa-plus-circle me-1"></i> إضافة خدمة جديدة
            </button>
            <button type="button" class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                <i class="fas fa-folder me-1"></i> إدارة الفئات
            </button>
        </div>
    </div>
    
    <?php
    // Process service form actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add new service
        if (isset($_POST['add_service'])) {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $min_quantity = filter_input(INPUT_POST, 'min_quantity', FILTER_VALIDATE_INT);
            $max_quantity = filter_input(INPUT_POST, 'max_quantity', FILTER_VALIDATE_INT);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $is_popular = isset($_POST['is_popular']) ? 1 : 0;
            $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
            $speed = filter_input(INPUT_POST, 'speed', FILTER_SANITIZE_STRING);
            $quality = filter_input(INPUT_POST, 'quality', FILTER_SANITIZE_STRING);
            $guarantee_days = filter_input(INPUT_POST, 'guarantee_days', FILTER_VALIDATE_INT);
            $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT) ?? 0;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            
            try {
                $insert_service_query = "INSERT INTO services (name, category_id, min_quantity, max_quantity, price, 
                                         is_popular, start_time, speed, quality, guarantee_days, display_order, description) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_service_query);
                $stmt->bind_param("siiiidsssiss", $name, $category_id, $min_quantity, $max_quantity, $price, 
                                 $is_popular, $start_time, $speed, $quality, $guarantee_days, $display_order, $description);
                
                if ($stmt->execute()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            تمت إضافة الخدمة بنجاح.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        حدث خطأ أثناء إضافة الخدمة: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
        
        // Update existing service
        if (isset($_POST['update_service'])) {
            $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $min_quantity = filter_input(INPUT_POST, 'min_quantity', FILTER_VALIDATE_INT);
            $max_quantity = filter_input(INPUT_POST, 'max_quantity', FILTER_VALIDATE_INT);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $is_popular = isset($_POST['is_popular']) ? 1 : 0;
            $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
            $speed = filter_input(INPUT_POST, 'speed', FILTER_SANITIZE_STRING);
            $quality = filter_input(INPUT_POST, 'quality', FILTER_SANITIZE_STRING);
            $guarantee_days = filter_input(INPUT_POST, 'guarantee_days', FILTER_VALIDATE_INT);
            $display_order = filter_input(INPUT_POST, 'display_order', FILTER_VALIDATE_INT) ?? 0;
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            
            try {
                $update_service_query = "UPDATE services SET name = ?, category_id = ?, min_quantity = ?, max_quantity = ?, 
                                        price = ?, is_popular = ?, start_time = ?, speed = ?, quality = ?, 
                                        guarantee_days = ?, display_order = ?, description = ? WHERE id = ?";
                $stmt = $conn->prepare($update_service_query);
                $stmt->bind_param("siiiidsssissi", $name, $category_id, $min_quantity, $max_quantity, $price, 
                                $is_popular, $start_time, $speed, $quality, $guarantee_days, $display_order, $description, $service_id);
                
                if ($stmt->execute()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            تم تحديث الخدمة بنجاح.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        حدث خطأ أثناء تحديث الخدمة: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
        
        // Delete service
        if (isset($_POST['delete_service'])) {
            $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
            
            try {
                // Check if service is being used in orders
                $check_orders_query = "SELECT COUNT(*) as count FROM orders WHERE service_id = ?";
                $stmt = $conn->prepare($check_orders_query);
                $stmt->bind_param("i", $service_id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                if ($result['count'] > 0) {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                            لا يمكن حذف الخدمة لأنها مستخدمة في ' . $result['count'] . ' طلب. يمكنك تعطيل الخدمة بدلاً من حذفها.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                } else {
                    $delete_service_query = "DELETE FROM services WHERE id = ?";
                    $stmt = $conn->prepare($delete_service_query);
                    $stmt->bind_param("i", $service_id);
                    
                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                تم حذف الخدمة بنجاح.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                    } else {
                        throw new Exception($stmt->error);
                    }
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        حدث خطأ أثناء حذف الخدمة: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
        
        // Add category
        if (isset($_POST['add_category'])) {
            $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING);
            $category_slug = filter_input(INPUT_POST, 'category_slug', FILTER_SANITIZE_STRING);
            $category_icon = filter_input(INPUT_POST, 'category_icon', FILTER_SANITIZE_STRING);
            $category_display_order = filter_input(INPUT_POST, 'category_display_order', FILTER_VALIDATE_INT) ?? 0;
            
            // Generate slug if not provided
            if (empty($category_slug)) {
                $category_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category_name)));
            }
            
            try {
                $insert_category_query = "INSERT INTO service_categories (name, slug, icon, display_order) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_category_query);
                $stmt->bind_param("sssi", $category_name, $category_slug, $category_icon, $category_display_order);
                
                if ($stmt->execute()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            تمت إضافة الفئة بنجاح.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        حدث خطأ أثناء إضافة الفئة: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
        
        // Update category
        if (isset($_POST['update_category'])) {
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING);
            $category_slug = filter_input(INPUT_POST, 'category_slug', FILTER_SANITIZE_STRING);
            $category_icon = filter_input(INPUT_POST, 'category_icon', FILTER_SANITIZE_STRING);
            $category_display_order = filter_input(INPUT_POST, 'category_display_order', FILTER_VALIDATE_INT) ?? 0;
            
            // Generate slug if not provided
            if (empty($category_slug)) {
                $category_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $category_name)));
            }
            
            try {
                $update_category_query = "UPDATE service_categories SET name = ?, slug = ?, icon = ?, display_order = ? WHERE id = ?";
                $stmt = $conn->prepare($update_category_query);
                $stmt->bind_param("sssii", $category_name, $category_slug, $category_icon, $category_display_order, $category_id);
                
                if ($stmt->execute()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            تم تحديث الفئة بنجاح.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        حدث خطأ أثناء تحديث الفئة: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
        
        // Delete category
        if (isset($_POST['delete_category'])) {
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            
            try {
                // Check if category has services
                $check_services_query = "SELECT COUNT(*) as count FROM services WHERE category_id = ?";
                $stmt = $conn->prepare($check_services_query);
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                if ($result['count'] > 0) {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                            لا يمكن حذف الفئة لأنها تحتوي على ' . $result['count'] . ' خدمة. قم بنقل الخدمات إلى فئة أخرى أولاً.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                } else {
                    $delete_category_query = "DELETE FROM service_categories WHERE id = ?";
                    $stmt = $conn->prepare($delete_category_query);
                    $stmt->bind_param("i", $category_id);
                    
                    if ($stmt->execute()) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                تم حذف الفئة بنجاح.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                    } else {
                        throw new Exception($stmt->error);
                    }
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        حدث خطأ أثناء حذف الفئة: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
    }
    
    // Get all categories for dropdowns
    $categories_query = "SELECT * FROM service_categories ORDER BY display_order, name";
    $categories = $conn->query($categories_query);
    $categories_list = array();
    if ($categories && $categories->num_rows > 0) {
        while ($category = $categories->fetch_assoc()) {
            $categories_list[] = $category;
        }
    }
    // Reset pointer
    $categories->data_seek(0);
    ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-fill" id="servicesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-services-tab" data-bs-toggle="tab" data-bs-target="#all-services" type="button" role="tab" aria-controls="all-services" aria-selected="true">
                        <i class="fas fa-th-list me-1"></i> جميع الخدمات
                    </button>
                </li>
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="category-<?php echo $category['id']; ?>-tab" data-bs-toggle="tab" data-bs-target="#category-<?php echo $category['id']; ?>" type="button" role="tab" aria-controls="category-<?php echo $category['id']; ?>" aria-selected="false">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                    </li>
                    <?php endwhile; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Search and Filter Bar -->
            <div class="bg-light p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="serviceSearchInput" placeholder="البحث باسم الخدمة...">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        <div class="d-flex align-items-center">
                            <label class="me-2 text-nowrap">عرض:</label>
                            <select class="form-select form-select-sm" id="entriesPerPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="servicesTabContent">
                <!-- All Services Tab -->
                <div class="tab-pane fade show active" id="all-services" role="tabpanel" aria-labelledby="all-services-tab">
                    <?php
                    $services_query = "SELECT s.*, c.name as category_name 
                                      FROM services s 
                                      JOIN service_categories c ON s.category_id = c.id 
                                      ORDER BY s.display_order, s.id DESC";
                    $services = $conn->query($services_query);
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped w-100" id="allServicesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>اسم الخدمة</th>
                                    <th>الفئة</th>
                                    <th class="text-center">الكمية (الحد الأدنى/الأقصى)</th>
                                    <th class="text-center">السعر</th>
                                    <th class="text-center">الجودة</th>
                                    <th class="text-center">مميزة</th>
                                    <th class="text-center" style="width: 120px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($services && $services->num_rows > 0): ?>
                                <?php while ($service = $services->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?php echo $service['id']; ?></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars($service['category_name']); ?></td>
                                    <td class="text-center"><?php echo number_format($service['min_quantity']); ?> / <?php echo number_format($service['max_quantity']); ?></td>
                                    <td class="text-center">$<?php echo number_format($service['price'], 3); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $quality_class = '';
                                        $quality_text = '';
                                        
                                        switch ($service['quality']) {
                                            case 'low':
                                                $quality_class = 'text-warning';
                                                $quality_text = 'عادية';
                                                break;
                                            case 'medium':
                                                $quality_class = 'text-info';
                                                $quality_text = 'متوسطة';
                                                break;
                                            case 'high':
                                                $quality_class = 'text-success';
                                                $quality_text = 'عالية';
                                                break;
                                            case 'premium':
                                                $quality_class = 'text-primary';
                                                $quality_text = 'ممتازة';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $quality_class; ?>"><?php echo $quality_text; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($service['is_popular']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewServiceModal<?php echo $service['id']; ?>" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $service['id']; ?>" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal<?php echo $service['id']; ?>" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- View Service Modal -->
                                <div class="modal fade" id="viewServiceModal<?php echo $service['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تفاصيل الخدمة: <?php echo htmlspecialchars($service['name']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>اسم الخدمة:</strong> <?php echo htmlspecialchars($service['name']); ?></p>
                                                        <p><strong>الفئة:</strong> <?php echo htmlspecialchars($service['category_name']); ?></p>
                                                        <p><strong>السعر:</strong> $<?php echo number_format($service['price'], 3); ?> لكل <?php echo number_format($service['min_quantity']); ?></p>
                                                        <p><strong>الحد الأدنى للطلب:</strong> <?php echo number_format($service['min_quantity']); ?></p>
                                                        <p><strong>الحد الأقصى للطلب:</strong> <?php echo number_format($service['max_quantity']); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>وقت البدء:</strong> <?php echo htmlspecialchars($service['start_time']); ?></p>
                                                        <p><strong>السرعة:</strong> <?php echo htmlspecialchars($service['speed']); ?></p>
                                                        <p><strong>الجودة:</strong> <span class="badge <?php echo $quality_class; ?>"><?php echo $quality_text; ?></span></p>
                                                        <p>
                                                            <strong>الضمان:</strong> 
                                                            <?php if ($service['guarantee_days'] > 0): ?>
                                                            <span class="badge bg-success"><?php echo $service['guarantee_days']; ?> يوم</span>
                                                            <?php else: ?>
                                                            <span class="badge bg-secondary">لا يوجد</span>
                                                            <?php endif; ?>
                                                        </p>
                                                        <p>
                                                            <strong>خدمة مميزة:</strong>
                                                            <?php if ($service['is_popular']): ?>
                                                            <span class="badge bg-success"><i class="fas fa-check"></i> نعم</span>
                                                            <?php else: ?>
                                                            <span class="badge bg-secondary"><i class="fas fa-times"></i> لا</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($service['description'])): ?>
                                                <hr>
                                                <h6>وصف الخدمة:</h6>
                                                <div class="bg-light p-3 rounded">
                                                    <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $service['id']; ?>" data-bs-dismiss="modal">
                                                    <i class="fas fa-edit me-1"></i> تعديل
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Edit Service Modal -->
                                <div class="modal fade" id="editServiceModal<?php echo $service['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">تعديل الخدمة: <?php echo htmlspecialchars($service['name']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" method="post" id="editServiceForm<?php echo $service['id']; ?>">
                                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="name<?php echo $service['id']; ?>" class="form-label">اسم الخدمة*</label>
                                                            <input type="text" class="form-control" id="name<?php echo $service['id']; ?>" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="col-md-6 mb-3">
                                                            <label for="category_id<?php echo $service['id']; ?>" class="form-label">الفئة*</label>
                                                            <select class="form-select" id="category_id<?php echo $service['id']; ?>" name="category_id" required>
                                                                <?php foreach ($categories_list as $category): ?>
                                                                <option value="<?php echo $category['id']; ?>" <?php echo ($service['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                                </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="min_quantity<?php echo $service['id']; ?>" class="form-label">الحد الأدنى للطلب*</label>
                                                            <input type="number" class="form-control" id="min_quantity<?php echo $service['id']; ?>" name="min_quantity" value="<?php echo $service['min_quantity']; ?>" min="1" required>
                                                        </div>
                                                        
                                                        <div class="col-md-6 mb-3">
                                                            <label for="max_quantity<?php echo $service['id']; ?>" class="form-label">الحد الأقصى للطلب*</label>
                                                            <input type="number" class="form-control" id="max_quantity<?php echo $service['id']; ?>" name="max_quantity" value="<?php echo $service['max_quantity']; ?>" min="1" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="price<?php echo $service['id']; ?>" class="form-label">السعر (بالدولار)*</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" id="price<?php echo $service['id']; ?>" name="price" value="<?php echo $service['price']; ?>" step="0.001" min="0.001" required>
                                                                <span class="input-group-text">$</span>
                                                            </div>
                                                            <div class="form-text">السعر لكل <?php echo number_format($service['min_quantity']); ?> وحدة</div>
                                                        </div>
                                                        
                                                        <div class="col-md-6 mb-3">
                                                            <label for="quality<?php echo $service['id']; ?>" class="form-label">الجودة*</label>
                                                            <select class="form-select" id="quality<?php echo $service['id']; ?>" name="quality" required>
                                                                <option value="low" <?php echo ($service['quality'] == 'low') ? 'selected' : ''; ?>>عادية</option>
                                                                <option value="medium" <?php echo ($service['quality'] == 'medium') ? 'selected' : ''; ?>>متوسطة</option>
                                                                <option value="high" <?php echo ($service['quality'] == 'high') ? 'selected' : ''; ?>>عالية</option>
                                                                <option value="premium" <?php echo ($service['quality'] == 'premium') ? 'selected' : ''; ?>>ممتازة</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="start_time<?php echo $service['id']; ?>" class="form-label">وقت البدء*</label>
                                                            <input type="text" class="form-control" id="start_time<?php echo $service['id']; ?>" name="start_time" value="<?php echo htmlspecialchars($service['start_time']); ?>" required>
                                                            <div class="form-text">مثال: فوري، 0-1 ساعة، 1-24 ساعة، الخ</div>
                                                        </div>
                                                        
                                                        <div class="col-md-6 mb-3">
                                                            <label for="speed<?php echo $service['id']; ?>" class="form-label">السرعة*</label>
                                                            <input type="text" class="form-control" id="speed<?php echo $service['id']; ?>" name="speed" value="<?php echo htmlspecialchars($service['speed']); ?>" required>
                                                            <div class="form-text">مثال: 1000/الساعة، متوسطة، بطيئة، الخ</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="guarantee_days<?php echo $service['id']; ?>" class="form-label">أيام الضمان</label>
                                                            <input type="number" class="form-control" id="guarantee_days<?php echo $service['id']; ?>" name="guarantee_days" value="<?php echo $service['guarantee_days']; ?>" min="0">
                                                            <div class="form-text">0 = بدون ضمان</div>
                                                        </div>
                                                        
                                                        <div class="col-md-6 mb-3">
                                                            <label for="display_order<?php echo $service['id']; ?>" class="form-label">ترتيب العرض</label>
                                                            <input type="number" class="form-control" id="display_order<?php echo $service['id']; ?>" name="display_order" value="<?php echo $service['display_order']; ?>" min="0">
                                                            <div class="form-text">0 = افتراضي (حسب الإضافة)</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="is_popular<?php echo $service['id']; ?>" name="is_popular" <?php echo $service['is_popular'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="is_popular<?php echo $service['id']; ?>">خدمة مميزة</label>
                                                        </div>
                                                        <div class="form-text">الخدمات المميزة تظهر في الصفحة الرئيسية وفي أعلى قوائم الخدمات</div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="description<?php echo $service['id']; ?>" class="form-label">وصف الخدمة</label>
                                                        <textarea class="form-control" id="description<?php echo $service['id']; ?>" name="description" rows="4"><?php echo htmlspecialchars($service['description']); ?></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" form="editServiceForm<?php echo $service['id']; ?>" name="update_service" class="btn btn-primary">حفظ التغييرات</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Delete Service Modal -->
                                <div class="modal fade" id="deleteServiceModal<?php echo $service['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">حذف الخدمة</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من أنك تريد حذف الخدمة: <strong><?php echo htmlspecialchars($service['name']); ?></strong>؟</p>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    هذا الإجراء لا يمكن التراجع عنه. إذا كانت الخدمة مستخدمة في طلبات سابقة، فلن يتم حذفها.
                                                </div>
                                                <form action="" method="post" id="deleteServiceForm<?php echo $service['id']; ?>">
                                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" form="deleteServiceForm<?php echo $service['id']; ?>" name="delete_service" class="btn btn-danger">نعم، حذف الخدمة</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center p-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p>لا توجد خدمات حتى الآن</p>
                                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                                <i class="fas fa-plus-circle me-1"></i> إضافة خدمة جديدة
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Category Tabs -->
                <?php 
                $categories->data_seek(0);
                if ($categories && $categories->num_rows > 0):
                    while ($category = $categories->fetch_assoc()):
                        $category_id = $category['id'];
                        $category_services_query = "SELECT s.*, c.name as category_name 
                                                  FROM services s 
                                                  JOIN service_categories c ON s.category_id = c.id 
                                                  WHERE s.category_id = $category_id 
                                                  ORDER BY s.display_order, s.id DESC";
                        $category_services = $conn->query($category_services_query);
                ?>
                <div class="tab-pane fade" id="category-<?php echo $category_id; ?>" role="tabpanel" aria-labelledby="category-<?php echo $category_id; ?>-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped w-100" id="category<?php echo $category_id; ?>ServicesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th>اسم الخدمة</th>
                                    <th class="text-center">الكمية (الحد الأدنى/الأقصى)</th>
                                    <th class="text-center">السعر</th>
                                    <th class="text-center">الجودة</th>
                                    <th class="text-center">مميزة</th>
                                    <th class="text-center" style="width: 120px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($category_services && $category_services->num_rows > 0): ?>
                                <?php while ($service = $category_services->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?php echo $service['id']; ?></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td class="text-center"><?php echo number_format($service['min_quantity']); ?> / <?php echo number_format($service['max_quantity']); ?></td>
                                    <td class="text-center">$<?php echo number_format($service['price'], 3); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $quality_class = '';
                                        $quality_text = '';
                                        
                                        switch ($service['quality']) {
                                            case 'low':
                                                $quality_class = 'text-warning';
                                                $quality_text = 'عادية';
                                                break;
                                            case 'medium':
                                                $quality_class = 'text-info';
                                                $quality_text = 'متوسطة';
                                                break;
                                            case 'high':
                                                $quality_class = 'text-success';
                                                $quality_text = 'عالية';
                                                break;
                                            case 'premium':
                                                $quality_class = 'text-primary';
                                                $quality_text = 'ممتازة';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $quality_class; ?>"><?php echo $quality_text; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($service['is_popular']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewServiceModal<?php echo $service['id']; ?>" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $service['id']; ?>" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal<?php echo $service['id']; ?>" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center p-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p>لا توجد خدمات في هذه الفئة</p>
                                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                                <i class="fas fa-plus-circle me-1"></i> إضافة خدمة جديدة
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php 
                    endwhile;
                endif;
                ?>
            </div>
        </div>
    </div>
    
    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة خدمة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" id="addServiceForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">اسم الخدمة*</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">الفئة*</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- اختر الفئة --</option>
                                    <?php foreach ($categories_list as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="min_quantity" class="form-label">الحد الأدنى للطلب*</label>
                                <input type="number" class="form-control" id="min_quantity" name="min_quantity" value="1000" min="1" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="max_quantity" class="form-label">الحد الأقصى للطلب*</label>
                                <input type="number" class="form-control" id="max_quantity" name="max_quantity" value="100000" min="1" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">السعر (بالدولار)*</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="price" name="price" value="1" step="0.001" min="0.001" required>
                                    <span class="input-group-text">$</span>
                                </div>
                                <div class="form-text">السعر لكل 1000 وحدة (أو الحد الأدنى المحدد)</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quality" class="form-label">الجودة*</label>
                                <select class="form-select" id="quality" name="quality" required>
                                    <option value="low">عادية</option>
                                    <option value="medium" selected>متوسطة</option>
                                    <option value="high">عالية</option>
                                    <option value="premium">ممتازة</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">وقت البدء*</label>
                                <input type="text" class="form-control" id="start_time" name="start_time" value="0-1 ساعة" required>
                                <div class="form-text">مثال: فوري، 0-1 ساعة، 1-24 ساعة، الخ</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="speed" class="form-label">السرعة*</label>
                                <input type="text" class="form-control" id="speed" name="speed" value="1000-2000/الساعة" required>
                                <div class="form-text">مثال: 1000/الساعة، متوسطة، بطيئة، الخ</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guarantee_days" class="form-label">أيام الضمان</label>
                                <input type="number" class="form-control" id="guarantee_days" name="guarantee_days" value="0" min="0">
                                <div class="form-text">0 = بدون ضمان</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">ترتيب العرض</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                                <div class="form-text">0 = افتراضي (حسب الإضافة)</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular">
                                <label class="form-check-label" for="is_popular">خدمة مميزة</label>
                            </div>
                            <div class="form-text">الخدمات المميزة تظهر في الصفحة الرئيسية وفي أعلى قوائم الخدمات</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف الخدمة</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" form="addServiceForm" name="add_service" class="btn btn-primary">إضافة الخدمة</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Manage Categories Modal -->
    <div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إدارة فئات الخدمات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Category Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-light py-3">
                            <h6 class="card-title mb-0">إضافة فئة جديدة</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="post" id="addCategoryForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_name" class="form-label">اسم الفئة*</label>
                                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="category_slug" class="form-label">الرابط المختصر (Slug)</label>
                                        <input type="text" class="form-control" id="category_slug" name="category_slug">
                                        <div class="form-text">سيتم إنشاؤه تلقائيًا إذا تركته فارغًا</div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_icon" class="form-label">رمز الفئة (أيقونة)</label>
                                        <input type="text" class="form-control" id="category_icon" name="category_icon" placeholder="مثال: images/icons/instagram.png">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="category_display_order" class="form-label">ترتيب العرض</label>
                                        <input type="number" class="form-control" id="category_display_order" name="category_display_order" value="0" min="0">
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="add_category" class="btn btn-primary">إضافة الفئة</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Categories List -->
                    <div class="card">
                        <div class="card-header bg-light py-3">
                            <h6 class="card-title mb-0">الفئات الحالية</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الرمز</th>
                                            <th>اسم الفئة</th>
                                            <th>الرابط المختصر</th>
                                            <th class="text-center">عدد الخدمات</th>
                                            <th class="text-center">الترتيب</th>
                                            <th class="text-center">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $categories->data_seek(0);
                                        if ($categories && $categories->num_rows > 0):
                                            while ($category = $categories->fetch_assoc()):
                                                // Count services in category
                                                $count_query = "SELECT COUNT(*) as count FROM services WHERE category_id = " . $category['id'];
                                                $count_result = $conn->query($count_query)->fetch_assoc();
                                                $service_count = $count_result['count'];
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($category['icon'])): ?>
                                                <img src="<?php echo htmlspecialchars($category['icon']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="img-fluid" style="max-width: 30px; max-height: 30px;">
                                                <?php else: ?>
                                                <i class="fas fa-folder text-secondary"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                            <td class="text-center"><?php echo $service_count; ?></td>
                                            <td class="text-center"><?php echo $category['display_order']; ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($service_count == 0): ?>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?php echo $category['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-danger" disabled title="لا يمكن حذف الفئة لأنها تحتوي على خدمات">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Category Modal -->
                                        <div class="modal fade" id="editCategoryModal<?php echo $category['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل الفئة: <?php echo htmlspecialchars($category['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="" method="post" id="editCategoryForm<?php echo $category['id']; ?>">
                                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="category_name<?php echo $category['id']; ?>" class="form-label">اسم الفئة*</label>
                                                                <input type="text" class="form-control" id="category_name<?php echo $category['id']; ?>" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="category_slug<?php echo $category['id']; ?>" class="form-label">الرابط المختصر (Slug)</label>
                                                                <input type="text" class="form-control" id="category_slug<?php echo $category['id']; ?>" name="category_slug" value="<?php echo htmlspecialchars($category['slug']); ?>">
                                                                <div class="form-text">سيتم إنشاؤه تلقائيًا إذا تركته فارغًا</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="category_icon<?php echo $category['id']; ?>" class="form-label">رمز الفئة (أيقونة)</label>
                                                                <input type="text" class="form-control" id="category_icon<?php echo $category['id']; ?>" name="category_icon" value="<?php echo htmlspecialchars($category['icon']); ?>">
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="category_display_order<?php echo $category['id']; ?>" class="form-label">ترتيب العرض</label>
                                                                <input type="number" class="form-control" id="category_display_order<?php echo $category['id']; ?>" name="category_display_order" value="<?php echo $category['display_order']; ?>" min="0">
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" form="editCategoryForm<?php echo $category['id']; ?>" name="update_category" class="btn btn-primary">حفظ التغييرات</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Category Modal -->
                                        <div class="modal fade" id="deleteCategoryModal<?php echo $category['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">حذف الفئة</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>هل أنت متأكد من أنك تريد حذف الفئة: <strong><?php echo htmlspecialchars($category['name']); ?></strong>؟</p>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            هذا الإجراء لا يمكن التراجع عنه.
                                                        </div>
                                                        <form action="" method="post" id="deleteCategoryForm<?php echo $category['id']; ?>">
                                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <button type="submit" form="deleteCategoryForm<?php echo $category['id']; ?>" name="delete_category" class="btn btn-danger">نعم، حذف الفئة</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">لا توجد فئات حتى الآن</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables for services
    $('#allServicesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
        },
        "pageLength": 10,
        "ordering": true,
        "responsive": true,
        "dom": 'rt<"bottom"ip>',
        "order": [[0, "desc"]]
    });
    
    // Initialize DataTables for category tabs
    <?php 
    $categories->data_seek(0);
    if ($categories && $categories->num_rows > 0):
        while ($category = $categories->fetch_assoc()):
    ?>
    $('#category<?php echo $category['id']; ?>ServicesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
        },
        "pageLength": 10,
        "ordering": true,
        "responsive": true,
        "dom": 'rt<"bottom"ip>',
        "order": [[0, "desc"]]
    });
    <?php endwhile; endif; ?>
    
    // Search functionality
    $("#serviceSearchInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#allServicesTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Change entries per page
    $("#entriesPerPage").change(function() {
        $('#allServicesTable').DataTable().page.len(parseInt($(this).val())).draw();
    });
    
    // Auto-generate slug from category name
    $("#category_name, [id^=category_name]").on('input', function() {
        var id = $(this).attr('id').replace('category_name', '');
        var slug = $(this).val().trim()
            .toLowerCase()
            .replace(/[\s\W-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        
        $("#category_slug" + id).val(slug);
    });
    
    // Form validation
    function validateServiceForm(formId) {
        var form = document.getElementById(formId);
        var minQuantity = parseInt(form.querySelector('[name="min_quantity"]').value);
        var maxQuantity = parseInt(form.querySelector('[name="max_quantity"]').value);
        
        if (minQuantity > maxQuantity) {
            alert("الحد الأدنى للطلب يجب أن يكون أقل من أو يساوي الحد الأقصى");
            return false;
        }
        
        return true;
    }
    
    // Add event listeners to service forms
    $("#addServiceForm").on('submit', function(e) {
        if (!validateServiceForm('addServiceForm')) {
            e.preventDefault();
        }
    });
    
    $("form[id^=editServiceForm]").each(function() {
        $(this).on('submit', function(e) {
            if (!validateServiceForm(this.id)) {
                e.preventDefault();
            }
        });
    });
});
</script>