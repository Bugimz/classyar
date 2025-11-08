INSERT INTO hero_roles (id, name_en, name_fa, description) VALUES
    (1, 'Tank', 'تانک', 'هیروهایی با مقاومت بالا برای جذب آسیب'),
    (2, 'Fighter', 'فایتر', 'ترکیبی از آسیب و مقاومت'),
    (3, 'Assassin', 'اسسین', 'هیروهایی با Burst Damage بالا'),
    (4, 'Mage', 'مج', 'هیروهای با آسیب جادویی بالا'),
    (5, 'Marksman', 'مارکسمن', 'حملات از راه دور با DPS بالا'),
    (6, 'Support', 'ساپورت', 'تمرکز بر کمک و محافظت از تیم')
ON DUPLICATE KEY UPDATE
    name_en = VALUES(name_en),
    name_fa = VALUES(name_fa),
    description = VALUES(description);
