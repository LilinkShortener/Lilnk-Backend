CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- شناسه یکتای کاربر که به صورت خودکار افزایش می‌یابد
    email VARCHAR(255) UNIQUE NOT NULL,  -- آدرس ایمیل یکتای کاربر که به عنوان نام کاربری استفاده می‌شود
    password_hash VARCHAR(255) NOT NULL,  -- هش رمز عبور کاربر برای ذخیره امن رمز عبور
    registration_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- زمان ثبت‌نام کاربر که به صورت خودکار تنظیم می‌شود
    total_earnings INT DEFAULT 0  -- مجموع درآمدهای کاربر از لینک‌های تبلیغاتی که به طور پیش‌فرض صفر است
);

CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- شناسه یکتای لینک که به صورت خودکار افزایش می‌یابد
    short_url VARCHAR(10) UNIQUE NOT NULL,  -- آدرس کوتاه‌شده لینک که یکتا و غیرتکراری است
    original_url TEXT NOT NULL,  -- آدرس اصلی لینک که توسط کاربر ارائه شده است
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- زمان ایجاد لینک که به صورت خودکار تنظیم می‌شود
    access_count INT DEFAULT 0,  -- تعداد دفعاتی که لینک کوتاه شده کلیک شده است که به طور پیش‌فرض صفر است
    last_accessed TIMESTAMP NULL,  -- زمان آخرین باری که لینک کلیک شده است، این مقدار می‌تواند NULL باشد
    user_id INT,  -- شناسه کاربری که این لینک را ایجاد کرده، به عنوان کلید خارجی به جدول کاربران اشاره می‌کند
    with_ads TINYINT(1) DEFAULT 1,  -- نشان می‌دهد که لینک دارای تبلیغات است (۱) یا خیر (۰)، به طور پیش‌فرض ۱ است
    earnings INT NOT NULL DEFAULT 0,  -- درآمد حاصل از این لینک برای کاربر که به طور پیش‌فرض صفر است
    FOREIGN KEY (user_id) REFERENCES users(id)  -- ارتباط لینک با کاربر ایجاد کننده آن از طریق کلید خارجی
);

CREATE TABLE withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- شناسه یکتای درخواست برداشت که به صورت خودکار افزایش می‌یابد
    user_id INT NOT NULL,  -- شناسه کاربر درخواست‌کننده برداشت، به عنوان کلید خارجی به جدول کاربران اشاره می‌کند
    iban VARCHAR(34) NOT NULL,  -- شماره شبا (IBAN) کاربر که برای انتقال وجه استفاده می‌شود
    amount INT NOT NULL DEFAULT 0,  -- مقدار برداشت درخواست‌شده توسط کاربر، پیش‌فرض صفر
    name VARCHAR(255) NOT NULL,  -- نام کاربر که برای ثبت درخواست برداشت لازم است
    surname VARCHAR(255) NOT NULL,  -- نام خانوادگی کاربر که برای ثبت درخواست برداشت لازم است
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- زمان ثبت درخواست برداشت که به صورت خودکار تنظیم می‌شود
    status TINYINT(1) DEFAULT 0,  -- وضعیت پردازش درخواست برداشت، ۰ به معنی در انتظار، ۱ به معنی تایید شده
    FOREIGN KEY (user_id) REFERENCES users(id)  -- ارتباط درخواست برداشت با کاربر ایجاد کننده آن از طریق کلید خارجی
);

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- شناسه یکتای نوت که به صورت خودکار افزایش می‌یابد
    user_id INT,  -- شناسه کاربری که این نوت را ایجاد کرده، به عنوان کلید خارجی به جدول کاربران اشاره می‌کند
    title VARCHAR(255),  -- تیتر نوت که در صورت null بودن title نشان داده نشود
    content TEXT NOT NULL,  -- محتوای نوت که به صورت متن طولانی ذخیره می‌شود
    short_url VARCHAR(10) UNIQUE NOT NULL,  -- آدرس کوتاه‌شده برای نوت که یکتا است
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- زمان ایجاد نوت
    updated_at TIMESTAMP NULL,  -- زمان آخرین باری که نوت ویرایش شده است، این مقدار می‌تواند NULL باشد
    access_count INT DEFAULT 0,  -- تعداد دفعاتی که نوت کلیک شده است که به طور پیش‌فرض صفر است
    last_accessed TIMESTAMP NULL,  -- زمان آخرین باری که نوت کلیک شده است، این مقدار می‌تواند NULL باشد
    FOREIGN KEY (user_id) REFERENCES users(id)  -- ارتباط نوت با کاربر ایجاد کننده آن از طریق کلید خارجی
);
