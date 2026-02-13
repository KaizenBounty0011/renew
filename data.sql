-- Renew Empire Database Schema
-- Run: mysql -u root < data.sql

CREATE DATABASE IF NOT EXISTS renew_empire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE renew_empire;

-- Admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin','division_manager') DEFAULT 'super_admin',
    division_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

-- Site Settings
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    category VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB;

-- Divisions
CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    division_name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    tagline VARCHAR(255),
    description TEXT,
    hero_image1 VARCHAR(255),
    hero_image2 VARCHAR(255),
    hero_image3 VARCHAR(255),
    content TEXT,
    accent_color VARCHAR(20) DEFAULT '#e8491d',
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

-- News
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    featured_image VARCHAR(255),
    author_id INT,
    category VARCHAR(50),
    division_id INT DEFAULT NULL,
    published_date DATE,
    views INT DEFAULT 0,
    status ENUM('published','draft') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Careers
CREATE TABLE IF NOT EXISTS careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    department VARCHAR(100),
    division_id INT DEFAULT NULL,
    location VARCHAR(150),
    employment_type ENUM('full_time','part_time','contract','internship') DEFAULT 'full_time',
    description TEXT,
    requirements TEXT,
    responsibilities TEXT,
    salary_range VARCHAR(100),
    application_deadline DATE,
    status ENUM('open','closed') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Job Applications
CREATE TABLE IF NOT EXISTS job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    career_id INT NOT NULL,
    applicant_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    resume_path VARCHAR(255),
    cover_letter TEXT,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('received','reviewing','shortlisted','rejected') DEFAULT 'received',
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Fight Events
CREATE TABLE IF NOT EXISTS fight_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    event_date DATETIME NOT NULL,
    venue VARCHAR(200),
    location VARCHAR(200),
    description TEXT,
    featured_image VARCHAR(255),
    ticket_price DECIMAL(10,2) DEFAULT 0,
    vip_price DECIMAL(10,2) DEFAULT 0,
    available_tickets INT DEFAULT 0,
    status ENUM('upcoming','completed','cancelled') DEFAULT 'upcoming',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Fight Bookings
CREATE TABLE IF NOT EXISTS fight_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    ticket_type ENUM('regular','vip') DEFAULT 'regular',
    ticket_quantity INT DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES fight_events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Entertainment Shows
CREATE TABLE IF NOT EXISTS entertainment_shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    show_name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    show_date DATETIME NOT NULL,
    venue VARCHAR(200),
    location VARCHAR(200),
    description TEXT,
    featured_image VARCHAR(255),
    ticket_price DECIMAL(10,2) DEFAULT 0,
    vip_price DECIMAL(10,2) DEFAULT 0,
    available_tickets INT DEFAULT 0,
    status ENUM('upcoming','completed','cancelled') DEFAULT 'upcoming',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Show Bookings
CREATE TABLE IF NOT EXISTS show_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    ticket_type ENUM('regular','vip') DEFAULT 'regular',
    ticket_quantity INT DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (show_id) REFERENCES entertainment_shows(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Hotels
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    location VARCHAR(200),
    address TEXT,
    description TEXT,
    featured_image VARCHAR(255),
    gallery_images TEXT,
    amenities TEXT,
    star_rating INT DEFAULT 5,
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

-- Rooms
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    capacity INT DEFAULT 2,
    available_rooms INT DEFAULT 1,
    images TEXT,
    amenities TEXT,
    status ENUM('available','unavailable') DEFAULT 'available',
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Room Reservations
CREATE TABLE IF NOT EXISTS room_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    hotel_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    guests_count INT DEFAULT 1,
    total_nights INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    special_requests TEXT,
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed','checked_in','checked_out','cancelled') DEFAULT 'confirmed',
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Energy Services
CREATE TABLE IF NOT EXISTS energy_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    detailed_content TEXT,
    featured_image VARCHAR(255),
    icon VARCHAR(50),
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

-- Energy Products
CREATE TABLE IF NOT EXISTS energy_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category VARCHAR(100),
    description TEXT,
    specifications TEXT,
    price DECIMAL(10,2),
    product_image VARCHAR(255),
    brochure_path VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Service Inquiries
CREATE TABLE IF NOT EXISTS service_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT DEFAULT NULL,
    company_name VARCHAR(200),
    contact_person VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    message TEXT,
    inquiry_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new','responded','closed') DEFAULT 'new',
    FOREIGN KEY (service_id) REFERENCES energy_services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Contact Inquiries
CREATE TABLE IF NOT EXISTS contact_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(30),
    department VARCHAR(50),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new','read','responded') DEFAULT 'new'
) ENGINE=InnoDB;

-- Media Gallery
CREATE TABLE IF NOT EXISTS media_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    media_type ENUM('image','video') DEFAULT 'image',
    file_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255),
    category VARCHAR(50),
    division_id INT DEFAULT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

-- Page Content
CREATE TABLE IF NOT EXISTS page_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(100) NOT NULL,
    section_name VARCHAR(100) NOT NULL,
    content TEXT,
    images TEXT,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_page_section (page_slug, section_name)
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin (password: admin123)
INSERT INTO admins (username, email, password, full_name, role) VALUES
('admin', 'admin@renewempire.com', '$2y$10$8KzQyR5Xk5X5X5X5X5X5XeNqJ9X5X5X5X5X5X5X5X5X5X5X5X5', 'System Administrator', 'super_admin');

-- We'll update the password with a proper hash
UPDATE admins SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';

-- Site Settings
INSERT INTO site_settings (setting_key, setting_value, category) VALUES
('site_name', 'Renew Empire', 'general'),
('site_tagline', 'Building Tomorrow, Today', 'general'),
('site_email', 'info@renewempire.com', 'contact'),
('site_phone', '+234 800 RENEW 00', 'contact'),
('site_address', '1 Renew Tower, Victoria Island, Lagos, Nigeria', 'contact'),
('facebook', 'https://facebook.com/renewempire', 'social'),
('twitter', 'https://twitter.com/renewempire', 'social'),
('instagram', 'https://instagram.com/renewempire', 'social'),
('linkedin', 'https://linkedin.com/company/renewempire', 'social'),
('youtube', 'https://youtube.com/renewempire', 'social'),
('footer_text', '© 2026 Renew Empire. All Rights Reserved.', 'general'),
('meta_description', 'Renew Empire - A diversified corporate group leading innovation across Fight Championship, Entertainment, Hotels, and Energy sectors.', 'seo');

-- Divisions
INSERT INTO divisions (division_name, slug, tagline, description, hero_image1, hero_image2, hero_image3, content, accent_color) VALUES
('Renew Fight Championship', 'fight-championship', 'Where Champions Are Made', 'Renew Fight Championship is the premier combat sports promotion in Africa, showcasing world-class mixed martial arts, boxing, and kickboxing events. We bring together the finest fighters from across the continent and beyond, delivering electrifying entertainment to millions of fans.', 'uploads/divisions/fight1.jpg', 'uploads/divisions/fight2.jpg', 'uploads/divisions/fight3.jpg', '<p>Founded with a vision to elevate African combat sports to the global stage, Renew Fight Championship has rapidly become the continent''s most prestigious fighting promotion. Our events feature state-of-the-art production, world-class venues, and the most talented fighters competing for glory.</p><p>From sold-out arenas to millions of online viewers, RFC delivers unmatched excitement and athletic excellence. We are committed to developing talent, promoting sportsmanship, and creating unforgettable experiences for our fans.</p>', '#e63946'),
('Renew Entertainment', 'entertainment', 'Experience the Extraordinary', 'Renew Entertainment is a full-spectrum entertainment company producing concerts, festivals, theatrical productions, and live events. We curate world-class entertainment experiences that bring people together and create lasting memories across Africa and beyond.', 'uploads/divisions/ent1.jpg', 'uploads/divisions/ent2.jpg', 'uploads/divisions/ent3.jpg', '<p>Renew Entertainment stands at the forefront of Africa''s entertainment revolution. From blockbuster concerts featuring international superstars to intimate theatrical productions, we create experiences that resonate with audiences of all ages.</p><p>Our portfolio includes music festivals, comedy shows, cultural events, and corporate entertainment solutions. With a team of industry veterans and creative visionaries, we consistently deliver productions that set new standards for excellence.</p>', '#9b59b6'),
('Renew Hotels', 'hotels', 'Luxury Redefined', 'Renew Hotels operates premium hospitality properties across key destinations in Africa. Our hotels combine world-class amenities with authentic African hospitality, offering guests an unparalleled experience of comfort, elegance, and personalized service.', 'uploads/divisions/hotel1.jpg', 'uploads/divisions/hotel2.jpg', 'uploads/divisions/hotel3.jpg', '<p>Renew Hotels represents the pinnacle of African hospitality. Each property in our portfolio is thoughtfully designed to reflect the unique character of its location while maintaining the consistent standard of luxury our guests expect.</p><p>From beachfront resorts to urban boutique hotels, every Renew Hotels property features exceptional dining, state-of-the-art facilities, and the warm, personalized service that defines our brand.</p>', '#1abc9c'),
('Renew Energy', 'energy', 'Powering Africa''s Future', 'Renew Energy is dedicated to accelerating Africa''s transition to sustainable energy. We develop, install, and maintain solar, wind, and hybrid energy solutions for commercial, industrial, and residential clients, driving economic growth while protecting the environment.', 'uploads/divisions/energy1.jpg', 'uploads/divisions/energy2.jpg', 'uploads/divisions/energy3.jpg', '<p>Renew Energy is at the forefront of Africa''s clean energy revolution. With a comprehensive portfolio of solar panels, wind turbines, energy storage systems, and hybrid solutions, we provide reliable, affordable, and sustainable power to communities and businesses across the continent.</p><p>Our team of engineers and energy specialists work closely with clients to design customized solutions that maximize efficiency and minimize environmental impact. From rural electrification projects to large-scale commercial installations, Renew Energy is powering progress.</p>', '#27ae60');

-- News
INSERT INTO news (title, slug, excerpt, content, featured_image, author_id, category, division_id, published_date, views, status) VALUES
('Renew Empire Announces Expansion into East Africa', 'renew-empire-expansion-east-africa', 'The corporate group reveals plans for new operations in Kenya, Tanzania, and Rwanda.', '<p>Renew Empire has announced a major expansion initiative targeting East African markets. The move will see the group establish new operations across Kenya, Tanzania, and Rwanda, creating thousands of jobs and bringing world-class services to the region.</p><p>CEO of Renew Empire stated: "East Africa represents an incredible opportunity for growth. We are committed to bringing our expertise in entertainment, hospitality, and energy to these vibrant markets."</p>', 'uploads/news/expansion.jpg', 1, 'Corporate', NULL, '2026-02-10', 245, 'published'),
('RFC 12: Battle of Champions Set for March', 'rfc-12-battle-of-champions', 'The biggest fight card of the year features five championship bouts.', '<p>Renew Fight Championship has announced RFC 12: Battle of Champions, the most anticipated event in the promotion''s history. The event will feature five championship fights across multiple weight classes.</p><p>Headlining the card is the highly anticipated rematch between heavyweight champion Thunder Okafor and challenger Steel Mensah. Tickets go on sale this Friday.</p>', 'uploads/news/rfc12.jpg', 1, 'Fight Championship', 1, '2026-02-08', 532, 'published'),
('Renew Hotels Opens Luxury Resort in Zanzibar', 'renew-hotels-zanzibar-resort', 'A stunning 200-room beachfront resort joins the Renew Hotels portfolio.', '<p>Renew Hotels has officially opened its newest property - a breathtaking 200-room luxury resort on the pristine shores of Zanzibar. The resort features world-class amenities including an infinity pool, spa, and five dining outlets.</p>', 'uploads/news/zanzibar.jpg', 1, 'Hotels', 3, '2026-02-05', 189, 'published'),
('Renew Energy Secures $50M Solar Farm Contract', 'renew-energy-solar-farm-contract', 'The energy division will develop a 100MW solar farm in Northern Nigeria.', '<p>Renew Energy has been awarded a $50 million contract to develop a 100-megawatt solar farm in Northern Nigeria. The project will provide clean electricity to over 200,000 households and create 500 construction jobs.</p>', 'uploads/news/solar.jpg', 1, 'Energy', 4, '2026-02-01', 312, 'published'),
('Summer Concert Series Announced by Renew Entertainment', 'summer-concert-series-2026', 'A lineup of international and local artists will perform across five cities.', '<p>Renew Entertainment has unveiled its highly anticipated Summer Concert Series for 2026. The series will span five major cities across West Africa, featuring an impressive lineup of international and local artists.</p>', 'uploads/news/concert.jpg', 1, 'Entertainment', 2, '2026-01-28', 421, 'published'),
('Renew Empire Reports Record Revenue for 2025', 'renew-empire-record-revenue-2025', 'The group''s annual report shows 35% year-over-year growth across all divisions.', '<p>Renew Empire has released its annual financial report, revealing record revenue and a 35% increase in year-over-year growth. All four divisions contributed to the outstanding performance, with Renew Energy leading the growth at 52%.</p>', 'uploads/news/revenue.jpg', 1, 'Corporate', NULL, '2026-01-20', 678, 'published');

-- Careers
INSERT INTO careers (job_title, slug, department, division_id, location, employment_type, description, requirements, responsibilities, salary_range, application_deadline, status) VALUES
('Senior Software Engineer', 'senior-software-engineer', 'Technology', NULL, 'Lagos, Nigeria', 'full_time', 'We are looking for an experienced software engineer to join our corporate technology team and help build scalable solutions across all Renew Empire divisions.', 'Bachelor''s degree in Computer Science or related field\n5+ years of professional software development experience\nProficiency in PHP, JavaScript, Python, or Java\nExperience with cloud platforms (AWS, GCP, Azure)\nStrong problem-solving skills', 'Design and develop high-quality software solutions\nCollaborate with cross-functional teams\nMentor junior developers\nParticipate in code reviews and architectural decisions\nOptimize application performance', '$80,000 - $120,000', '2026-04-30', 'open'),
('Event Coordinator', 'event-coordinator', 'Operations', 1, 'Lagos, Nigeria', 'full_time', 'Join Renew Fight Championship as an Event Coordinator, managing logistics for our world-class combat sports events.', 'Bachelor''s degree in Event Management or related field\n3+ years experience in event coordination\nExcellent organizational skills\nAbility to work under pressure\nKnowledge of combat sports is a plus', 'Plan and execute fight events from concept to completion\nCoordinate with venues, vendors, and fighters\nManage event budgets and timelines\nEnsure compliance with safety regulations\nOversee event day operations', '$40,000 - $55,000', '2026-03-31', 'open'),
('Hotel General Manager', 'hotel-general-manager', 'Hospitality', 3, 'Zanzibar, Tanzania', 'full_time', 'Lead our newest luxury resort in Zanzibar, ensuring exceptional guest experiences and operational excellence.', 'Bachelor''s degree in Hospitality Management\n10+ years experience in luxury hotel management\nProven leadership and team management skills\nFluent in English; Swahili is a plus\nInternational hospitality brand experience preferred', 'Oversee all hotel operations and staff\nDevelop and implement service standards\nManage P&L and budgets\nDrive guest satisfaction scores\nRepresent the hotel in the community', '$90,000 - $130,000', '2026-03-15', 'open'),
('Solar Installation Technician', 'solar-installation-technician', 'Engineering', 4, 'Abuja, Nigeria', 'full_time', 'Install and maintain solar energy systems for Renew Energy''s growing client base across Nigeria.', 'Technical diploma in Electrical Engineering or Solar Technology\n2+ years experience in solar installation\nKnowledge of PV systems and inverters\nValid driver''s license\nWillingness to travel', 'Install solar panels and mounting systems\nConnect electrical components and inverters\nPerform system testing and commissioning\nProvide maintenance and troubleshooting\nDocument installation work', '$25,000 - $35,000', '2026-04-15', 'open');

-- Fight Events
INSERT INTO fight_events (event_name, slug, event_date, venue, location, description, featured_image, ticket_price, vip_price, available_tickets, status) VALUES
('RFC 12: Battle of Champions', 'rfc-12-battle-of-champions', '2026-03-22 19:00:00', 'Eko Convention Centre', 'Lagos, Nigeria', 'The biggest fight card of the year! Five championship bouts featuring Africa''s finest fighters. Headlined by the heavyweight championship rematch between Thunder Okafor and Steel Mensah.', 'uploads/divisions/fight_event1.jpg', 15000.00, 50000.00, 5000, 'upcoming'),
('RFC 13: Rising Stars', 'rfc-13-rising-stars', '2026-04-19 18:00:00', 'Accra Sports Stadium', 'Accra, Ghana', 'The next generation of African fighting talent takes center stage. Featuring 8 exciting bouts with emerging contenders looking to make their mark.', 'uploads/divisions/fight_event2.jpg', 10000.00, 35000.00, 8000, 'upcoming'),
('RFC 14: Nairobi Nights', 'rfc-14-nairobi-nights', '2026-05-17 19:00:00', 'Kasarani Indoor Arena', 'Nairobi, Kenya', 'RFC makes its East African debut with a stacked card of international and local fighters. Don''t miss this historic event!', 'uploads/divisions/fight_event3.jpg', 12000.00, 40000.00, 6000, 'upcoming');

-- Entertainment Shows
INSERT INTO entertainment_shows (show_name, slug, show_date, venue, location, description, featured_image, ticket_price, vip_price, available_tickets, status) VALUES
('Afrobeats Summer Fest 2026', 'afrobeats-summer-fest-2026', '2026-04-05 16:00:00', 'Tafawa Balewa Square', 'Lagos, Nigeria', 'The ultimate Afrobeats experience featuring top artists from across Africa. A full day of music, culture, and celebration under the stars.', 'uploads/divisions/show1.jpg', 20000.00, 75000.00, 15000, 'upcoming'),
('Comedy Night Live: African Kings of Comedy', 'comedy-night-live', '2026-03-15 20:00:00', 'Eko Hotel Convention Centre', 'Lagos, Nigeria', 'An evening of non-stop laughter with Africa''s funniest comedians. Featuring international headliners and rising comedy stars.', 'uploads/divisions/show2.jpg', 12000.00, 40000.00, 3000, 'upcoming'),
('The Jazz Lounge Experience', 'jazz-lounge-experience', '2026-04-25 19:00:00', 'The Renew Grand Ballroom', 'Abuja, Nigeria', 'An intimate evening of world-class jazz performances, fine dining, and premium cocktails. Limited seating for an exclusive experience.', 'uploads/divisions/show3.jpg', 25000.00, 60000.00, 500, 'upcoming');

-- Hotels
INSERT INTO hotels (hotel_name, slug, location, address, description, featured_image, amenities, star_rating) VALUES
('Renew Grand Lagos', 'renew-grand-lagos', 'Lagos, Nigeria', '15 Admiralty Way, Lekki Phase 1, Lagos', 'Experience unparalleled luxury at Renew Grand Lagos, our flagship property offering breathtaking ocean views, world-class dining, and exceptional service in the heart of Lagos.', 'uploads/hotels/lagos.jpg', 'Swimming Pool,Spa & Wellness,Fine Dining,Business Centre,Gym,Concierge,Valet Parking,Beach Access,Free Wi-Fi,Room Service', 5),
('Renew Resort Zanzibar', 'renew-resort-zanzibar', 'Zanzibar, Tanzania', 'Nungwi Beach, North Zanzibar', 'A stunning beachfront paradise where crystal-clear waters meet world-class hospitality. Renew Resort Zanzibar is the ultimate tropical escape.', 'uploads/hotels/zanzibar.jpg', 'Private Beach,Infinity Pool,Spa,Water Sports,Fine Dining,Bar & Lounge,Kids Club,Airport Transfer,Free Wi-Fi,Diving Centre', 5),
('Renew Boutique Abuja', 'renew-boutique-abuja', 'Abuja, Nigeria', '22 Maitama Sule Street, Maitama, Abuja', 'An intimate boutique hotel in the diplomatic heart of Abuja, offering personalized service, elegant rooms, and a tranquil garden setting.', 'uploads/hotels/abuja.jpg', 'Garden Terrace,Restaurant,Bar,Business Centre,Gym,Free Wi-Fi,Airport Shuttle,Laundry,Concierge,Meeting Rooms', 4);

-- Rooms
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, capacity, available_rooms, amenities) VALUES
(1, 'Deluxe Room', 'Spacious room with city views, king-size bed, and modern amenities for a comfortable stay.', 45000.00, 2, 30, 'King Bed,City View,Mini Bar,Safe,Work Desk,Rain Shower,Smart TV'),
(1, 'Executive Suite', 'Premium suite with separate living area, panoramic ocean views, and exclusive lounge access.', 85000.00, 2, 15, 'King Bed,Ocean View,Living Area,Mini Bar,Safe,Jacuzzi,Lounge Access,Butler Service'),
(1, 'Presidential Suite', 'Our most luxurious accommodation with private terrace, dining room, and personalized butler service.', 250000.00, 4, 3, 'Master Bedroom,Private Terrace,Dining Room,Kitchen,Butler Service,Jacuzzi,Ocean View,Private Bar'),
(2, 'Ocean View Room', 'Wake up to stunning Indian Ocean views in this beautifully appointed beachfront room.', 55000.00, 2, 40, 'Queen Bed,Ocean View,Balcony,Mini Bar,Rain Shower,Beach Access'),
(2, 'Beach Villa', 'Private villa steps from the sand with plunge pool, outdoor shower, and direct beach access.', 120000.00, 2, 10, 'King Bed,Private Pool,Beach Access,Outdoor Shower,Butler Service,Mini Bar,Terrace'),
(2, 'Honeymoon Suite', 'Romantic retreat with four-poster bed, private jacuzzi, and sunset terrace.', 95000.00, 2, 5, 'Four-Poster Bed,Private Jacuzzi,Sunset Terrace,Champagne,Rose Petals,Butler Service'),
(3, 'Classic Room', 'Elegantly furnished room with garden views and complimentary breakfast.', 35000.00, 2, 20, 'Queen Bed,Garden View,Work Desk,Mini Bar,Rain Shower,Breakfast Included'),
(3, 'Garden Suite', 'Luxurious suite overlooking our manicured gardens with private sitting area.', 60000.00, 2, 8, 'King Bed,Garden View,Sitting Area,Mini Bar,Bathtub,Work Desk,Breakfast Included');

-- Energy Services
INSERT INTO energy_services (service_name, slug, description, detailed_content, featured_image, icon) VALUES
('Solar Panel Installation', 'solar-panel-installation', 'Complete design, supply, and installation of high-efficiency solar panel systems for residential, commercial, and industrial applications.', '<p>Our solar panel installation service covers the entire process from initial site assessment to final commissioning. We use only tier-1 solar panels and industry-leading inverters to ensure maximum energy generation and system longevity.</p><p>Our expert team handles everything including structural assessment, electrical design, permitting, installation, and grid connection. Every installation comes with comprehensive monitoring and a 25-year performance warranty.</p>', 'uploads/divisions/solar.jpg', 'fa-solar-panel'),
('Wind Energy Solutions', 'wind-energy-solutions', 'Small to medium-scale wind turbine systems for commercial and community power generation.', '<p>Harness the power of wind with our range of turbine solutions designed for African conditions. From small rooftop turbines to medium-scale community wind farms, we provide reliable wind energy systems that complement solar installations.</p>', 'uploads/divisions/wind.jpg', 'fa-wind'),
('Energy Storage Systems', 'energy-storage-systems', 'Advanced battery storage solutions to ensure uninterrupted power supply and energy independence.', '<p>Our energy storage systems use cutting-edge lithium-ion and lithium iron phosphate (LFP) battery technology to store excess energy for use during peak hours or power outages. Perfect for both grid-tied and off-grid applications.</p>', 'uploads/divisions/battery.jpg', 'fa-battery-full'),
('Energy Consulting', 'energy-consulting', 'Expert energy audits, feasibility studies, and strategic planning for organizations seeking to optimize their energy use.', '<p>Our consulting team provides comprehensive energy assessments to help organizations identify opportunities for cost savings, efficiency improvements, and renewable energy integration. We develop customized energy strategies aligned with your business goals.</p>', 'uploads/divisions/consulting.jpg', 'fa-chart-line');

-- Energy Products
INSERT INTO energy_products (product_name, slug, category, description, specifications, price, product_image) VALUES
('SolarMax 400W Monocrystalline Panel', 'solarmax-400w-panel', 'Solar Panels', 'High-efficiency monocrystalline solar panel with 21.3% efficiency rating. Ideal for residential and commercial installations.', 'Power Output: 400W\nEfficiency: 21.3%\nDimensions: 1755 x 1038 x 35mm\nWeight: 21.5kg\nWarranty: 25 years\nCell Type: Monocrystalline PERC', 85000.00, 'uploads/products/panel400.jpg'),
('PowerVault 10kWh Battery', 'powervault-10kwh-battery', 'Energy Storage', 'Compact lithium iron phosphate battery system with 10kWh capacity. Features integrated battery management system and 10-year warranty.', 'Capacity: 10kWh\nVoltage: 48V\nCycle Life: 6000+ cycles\nEfficiency: 95%\nWeight: 120kg\nWarranty: 10 years\nChemistry: LiFePO4', 950000.00, 'uploads/products/battery10.jpg'),
('HybridMax 5kW Inverter', 'hybridmax-5kw-inverter', 'Inverters', 'All-in-one hybrid inverter supporting solar, battery, and grid inputs. Smart monitoring via mobile app.', 'Power Rating: 5kW\nMPPT Channels: 2\nBattery Voltage: 48V\nEfficiency: 97.6%\nMonitoring: Wi-Fi/App\nWarranty: 5 years', 450000.00, 'uploads/products/inverter5.jpg'),
('WindPower 3kW Turbine', 'windpower-3kw-turbine', 'Wind Turbines', 'Compact horizontal axis wind turbine suitable for urban and semi-urban installations. Low noise design with automatic yaw system.', 'Rated Power: 3kW\nStart Wind Speed: 2.5 m/s\nRated Wind Speed: 11 m/s\nRotor Diameter: 3.2m\nWeight: 85kg\nWarranty: 5 years', 750000.00, 'uploads/products/turbine3.jpg'),
('SolarMax 550W Commercial Panel', 'solarmax-550w-commercial', 'Solar Panels', 'Ultra-high efficiency bifacial solar panel designed for large-scale commercial and utility projects.', 'Power Output: 550W\nEfficiency: 22.1%\nBifacial Gain: Up to 30%\nDimensions: 2278 x 1134 x 35mm\nWeight: 28.6kg\nWarranty: 30 years', 120000.00, 'uploads/products/panel550.jpg'),
('ChargePro EV Station', 'chargepro-ev-station', 'EV Charging', 'Level 2 electric vehicle charging station for homes and businesses. Smart scheduling and energy management features.', 'Power: 7.4kW / 22kW\nConnector: Type 2\nCable Length: 5m\nSmart Features: App Control, Scheduling\nProtection: IP65\nWarranty: 3 years', 350000.00, 'uploads/products/evcharger.jpg');

-- Page Content
INSERT INTO page_content (page_slug, section_name, content) VALUES
('about', 'story', '<p>Renew Empire was founded in 2018 with a bold vision: to build a diversified African conglomerate that sets new standards of excellence across multiple industries. What began as a single venture has grown into a powerhouse of four distinct divisions, each leading its respective sector.</p><p>Our journey is one of relentless ambition, strategic growth, and unwavering commitment to the communities we serve. From the electrifying world of combat sports to the serene luxury of our hotels, from cutting-edge entertainment to sustainable energy solutions, Renew Empire is redefining what African businesses can achieve on the global stage.</p>'),
('about', 'mission', 'To build world-class businesses that drive economic growth, create employment, and deliver exceptional experiences across Africa and beyond.'),
('about', 'vision', 'To be Africa''s most respected and innovative conglomerate, setting global standards in every industry we enter.'),
('about', 'values', 'Excellence: We pursue the highest standards in everything we do.\nInnovation: We embrace new ideas and technologies to stay ahead.\nIntegrity: We conduct business with transparency and ethical principles.\nCommunity: We invest in the communities where we operate.\nSustainability: We build for the long term, respecting people and planet.');
