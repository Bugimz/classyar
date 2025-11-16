<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $db->beginTransaction();

    $categoryConfigs = [
        ['slug' => 'attack', 'name_en' => 'Attack', 'name_fa' => 'حمله', 'sort_order' => 1],
        ['slug' => 'magic', 'name_en' => 'Magic', 'name_fa' => 'جادو', 'sort_order' => 2],
        ['slug' => 'defense', 'name_en' => 'Defense', 'name_fa' => 'دفاع', 'sort_order' => 3],
        ['slug' => 'movement', 'name_en' => 'Movement', 'name_fa' => 'حرکت', 'sort_order' => 4],
        ['slug' => 'jungling', 'name_en' => 'Jungling', 'name_fa' => 'جنگل', 'sort_order' => 5],
        ['slug' => 'roaming', 'name_en' => 'Roaming', 'name_fa' => 'گردش', 'sort_order' => 6],
    ];

    $categoryLookup = $db->prepare('SELECT id FROM item_categories WHERE slug = :slug LIMIT 1');
    $categoryInsert = $db->prepare('INSERT INTO item_categories (slug, name_en, name_fa, description_en, description_fa, sort_order) VALUES (:slug, :name_en, :name_fa, :description_en, :description_fa, :sort_order)');
    $categoryUpdate = $db->prepare('UPDATE item_categories SET name_en = :name_en, name_fa = :name_fa, description_en = :description_en, description_fa = :description_fa, sort_order = :sort_order WHERE id = :id');

    $categoryIds = [];
    foreach ($categoryConfigs as $category) {
        $categoryLookup->execute([':slug' => $category['slug']]);
        $existingId = $categoryLookup->fetchColumn();

        $payload = [
            ':slug' => $category['slug'],
            ':name_en' => $category['name_en'],
            ':name_fa' => $category['name_fa'],
            ':description_en' => $category['description_en'] ?? null,
            ':description_fa' => $category['description_fa'] ?? null,
            ':sort_order' => $category['sort_order'] ?? 0,
        ];

        if ($existingId) {
            $payload[':id'] = (int) $existingId;
            $categoryUpdate->execute($payload);
            $categoryIds[$category['slug']] = (int) $existingId;
        } else {
            $categoryInsert->execute($payload);
            $categoryIds[$category['slug']] = (int) $db->lastInsertId();
        }
    }

    $itemsConfig = [
        [
            'slug' => 'sea-halberd',
            'category_slug' => 'attack',
            'name_en' => 'Sea Halberd',
            'name_fa' => 'سی هالبرد',
            'tier' => 'Advanced',
            'gold_cost' => 2100,
            'stats_en' => '+70 Physical Attack, +25% Attack Speed',
            'stats_fa' => '+۷۰ حمله فیزیکی، +۲۵٪ سرعت حمله',
            'passive_en' => "Unique Passive - Punish: Basic attacks reduce target's shield and HP regen by 50% for 3s.",
            'passive_fa' => 'پسیو یکتا - Punish: حملات معمولی، سپر و ریجن هدف را به مدت ۳ ثانیه ۵۰٪ کاهش می‌دهد.',
            'description_en' => 'Anti-regen tool to counter durable heroes.',
            'description_fa' => 'ابزاری ضد ریجن برای مقابله با هیروهای مقاوم.',
            'image_url' => '/assets/uploads/items/sea_halberd.png',
        ],
        [
            'slug' => 'glowing-wand',
            'category_slug' => 'magic',
            'name_en' => 'Glowing Wand',
            'name_fa' => 'گلوینگ وند',
            'tier' => 'Tier 3',
            'gold_cost' => 2050,
            'stats_en' => '+75 Magic Power, +400 HP, +5% Movement Speed',
            'stats_fa' => '+۷۵ قدرت جادو، +۴۰۰ سلامتی، +۵٪ سرعت حرکت',
            'passive_en' => 'Unique Passive - Scorch: Skills burn targets for 3s dealing magic damage based on target HP.',
            'passive_fa' => 'پسیو یکتا - Scorch: مهارت‌ها، هدف را به مدت ۳ ثانیه سوزانده و بر اساس HP، آسیب جادویی وارد می‌کنند.',
            'description_en' => 'Burns tanks with percent HP damage over time.',
            'description_fa' => 'تانک‌ها را با آسیب تدریجی درصدی می‌سوزاند.',
            'image_url' => '/assets/uploads/items/glowing_wand.png',
        ],
        [
            'slug' => 'dominance-ice',
            'category_slug' => 'defense',
            'name_en' => 'Dominance Ice',
            'name_fa' => 'دومیننس آیس',
            'tier' => 'Tier 3',
            'gold_cost' => 2010,
            'stats_en' => '+70 Physical Defense, +5% Movement Speed, +500 Mana',
            'stats_fa' => '+۷۰ دفاع فیزیکی، +۵٪ سرعت حرکت، +۵۰۰ مانا',
            'passive_en' => "Unique Passive - Arctic Cold: Reduces nearby enemies' shield and HP regen by 50%.",
            'passive_fa' => 'پسیو یکتا - Arctic Cold: ریجن و سپر دشمنان اطراف را ۵۰٪ کاهش می‌دهد.',
            'description_en' => 'Classic anti-heal aura against sustain heroes.',
            'description_fa' => 'آیتم هاله‌ای برای کاهش ریجن دشمنان.',
            'image_url' => '/assets/uploads/items/dominance_ice.png',
        ],
        [
            'slug' => 'malefic-roar',
            'category_slug' => 'attack',
            'name_en' => 'Malefic Roar',
            'name_fa' => 'میلفیک روار',
            'tier' => 'Tier 3',
            'gold_cost' => 2060,
            'stats_en' => '+60 Physical Attack',
            'stats_fa' => '+۶۰ حمله فیزیکی',
            'passive_en' => 'Unique Passive - Armor Buster: +35% physical penetration; extra penetration against structures.',
            'passive_fa' => 'پسیو یکتا - Armor Buster: ۳۵٪ نفوذ زره و نفوذ اضافی در برابر سازه‌ها.',
            'description_en' => 'Essential armor penetration for marksmen and fighters.',
            'description_fa' => 'نفوذ زره مهم برای مارکسمن‌ها و فایترها.',
            'image_url' => '/assets/uploads/items/malefic_roar.png',
        ],
        [
            'slug' => 'genius-wand',
            'category_slug' => 'magic',
            'name_en' => 'Genius Wand',
            'name_fa' => 'جینیوس وند',
            'tier' => 'Tier 3',
            'gold_cost' => 2000,
            'stats_en' => '+75 Magic Power, +5% Movement Speed',
            'stats_fa' => '+۷۵ قدرت جادو، +۵٪ سرعت حرکت',
            'passive_en' => 'Unique Passive - Magic: Dealing damage reduces enemy magic defense by 3-10 for 2s, stacking up to 3 times.',
            'passive_fa' => 'پسیو یکتا - Magic: وارد کردن آسیب، دفاع جادویی دشمن را ۳ تا ۱۰ واحد به مدت ۲ ثانیه کاهش می‌دهد (تا ۳ بار).',
            'description_en' => 'Magic penetration stick for burst mages.',
            'description_fa' => 'چوب نفوذ جادویی برای مِیج‌های بَرسْت.',
            'image_url' => '/assets/uploads/items/genius_wand.png',
        ],
        [
            'slug' => 'divine-glaive',
            'category_slug' => 'magic',
            'name_en' => 'Divine Glaive',
            'name_fa' => 'دی‌واین گلیو',
            'tier' => 'Tier 3',
            'gold_cost' => 1970,
            'stats_en' => '+65 Magic Power',
            'stats_fa' => '+۶۵ قدرت جادو',
            'passive_en' => 'Unique Passive - Spellbreaker: Gains 0.1% magic penetration for each magic defense the enemy has.',
            'passive_fa' => 'پسیو یکتا - Spellbreaker: به ازای هر واحد دفاع جادو دشمن، ۰٫۱٪ نفوذ جادو می‌گیرد.',
            'description_en' => 'High penetration for late-game mages.',
            'description_fa' => 'نفوذ جادویی بالا برای مِیج‌های لِیت‌گیم.',
            'image_url' => '/assets/uploads/items/divine_glaive.png',
        ],
        [
            'slug' => 'demon-hunter-sword',
            'category_slug' => 'attack',
            'name_en' => 'Demon Hunter Sword',
            'name_fa' => 'دمون هانتر سورد',
            'tier' => 'Tier 3',
            'gold_cost' => 2180,
            'stats_en' => '+35 Physical Attack, +25% Attack Speed',
            'stats_fa' => '+۳۵ حمله فیزیکی، +۲۵٪ سرعت حمله',
            'passive_en' => "Unique Passive - Devour: Basic attacks deal extra damage equal to 9% of target's current HP.",
            'passive_fa' => 'پسیو یکتا - Devour: حملات معمولی، ۹٪ از جان فعلی هدف را به‌صورت آسیب اضافی وارد می‌کنند.',
            'description_en' => 'Percent HP damage to melt tanks.',
            'description_fa' => 'آسیب درصدی برای ذوب کردن تانک‌ها.',
            'image_url' => '/assets/uploads/items/demon_hunter_sword.png',
        ],
        [
            'slug' => 'wishing-lantern',
            'category_slug' => 'roaming',
            'name_en' => 'Wishing Lantern',
            'name_fa' => 'ویشینگ لنترن',
            'tier' => 'Roam',
            'gold_cost' => 2040,
            'stats_en' => '+30 Physical Defense, +30 Magic Defense, +8% Movement Speed',
            'stats_fa' => '+۳۰ دفاع فیزیکی، +۳۰ دفاع جادو، +۸٪ سرعت حرکت',
            'passive_en' => 'Unique Passive - Devotion & Thriving: Shares gold/exp with teammates and grants team utility buffs.',
            'passive_fa' => 'پسیو یکتا - Devotion & Thriving: طلای مشترک با تیم و افزایش اثرات حمایتی.',
            'description_en' => 'Roaming blessing lantern that scales with assists.',
            'description_fa' => 'آیتم رووم با تمرکز بر حمایت و امتیازات تیمی.',
            'image_url' => '/assets/uploads/items/wishing_lantern.png',
        ],
    ];

    $itemLookup = $db->prepare('SELECT id FROM items WHERE slug = :slug LIMIT 1');
    $itemInsert = $db->prepare('INSERT INTO items (category_id, slug, name_en, name_fa, tier, gold_cost, stats_en, stats_fa, passive_en, passive_fa, active_en, active_fa, description_en, description_fa, image_url) VALUES (:category_id, :slug, :name_en, :name_fa, :tier, :gold_cost, :stats_en, :stats_fa, :passive_en, :passive_fa, :active_en, :active_fa, :description_en, :description_fa, :image_url)');
    $itemUpdate = $db->prepare('UPDATE items SET category_id = :category_id, name_en = :name_en, name_fa = :name_fa, tier = :tier, gold_cost = :gold_cost, stats_en = :stats_en, stats_fa = :stats_fa, passive_en = :passive_en, passive_fa = :passive_fa, active_en = :active_en, active_fa = :active_fa, description_en = :description_en, description_fa = :description_fa, image_url = :image_url WHERE id = :id');

    $itemIds = [];
    foreach ($itemsConfig as $item) {
        $categorySlug = $item['category_slug'];
        if (!isset($categoryIds[$categorySlug])) {
            throw new RuntimeException('Category not seeded: ' . $categorySlug);
        }

        $itemLookup->execute([':slug' => $item['slug']]);
        $existingId = $itemLookup->fetchColumn();

        $payload = [
            ':category_id' => $categoryIds[$categorySlug],
            ':slug' => $item['slug'],
            ':name_en' => $item['name_en'],
            ':name_fa' => $item['name_fa'],
            ':tier' => $item['tier'] ?? null,
            ':gold_cost' => $item['gold_cost'] ?? null,
            ':stats_en' => $item['stats_en'] ?? null,
            ':stats_fa' => $item['stats_fa'] ?? null,
            ':passive_en' => $item['passive_en'] ?? null,
            ':passive_fa' => $item['passive_fa'] ?? null,
            ':active_en' => $item['active_en'] ?? null,
            ':active_fa' => $item['active_fa'] ?? null,
            ':description_en' => $item['description_en'] ?? null,
            ':description_fa' => $item['description_fa'] ?? null,
            ':image_url' => $item['image_url'] ?? null,
        ];

        if ($existingId) {
            $payload[':id'] = (int) $existingId;
            $itemUpdate->execute($payload);
            $itemIds[$item['slug']] = (int) $existingId;
        } else {
            $itemInsert->execute($payload);
            $itemIds[$item['slug']] = (int) $db->lastInsertId();
        }
    }

    $heroStmt = $db->prepare("INSERT INTO heroes (name_en, name_fa, role_id, specialty, hp, hp_regen, mana, mana_regen, physical_attack, magic_power, physical_defense, magic_defense, attack_speed, movement_speed, description, tips, difficulty, image_url)
        VALUES (:name_en, :name_fa, :role_id, :specialty, :hp, :hp_regen, :mana, :mana_regen, :physical_attack, :magic_power, :physical_defense, :magic_defense, :attack_speed, :movement_speed, :description, :tips, :difficulty, :image_url)");

    $heroStmt->execute([
        ':name_en' => 'Gloo',
        ':name_fa' => 'گلو',
        ':role_id' => 1,
        ':specialty' => 'Regen/Engage',
        ':hp' => 2819,
        ':hp_regen' => 50.0,
        ':mana' => 500,
        ':mana_regen' => 16.0,
        ':physical_attack' => 120,
        ':magic_power' => 0,
        ':physical_defense' => 23,
        ':magic_defense' => 15,
        ':attack_speed' => 0.83,
        ':movement_speed' => 260,
        ':description' => 'گلو یک تانک منحصر به فرد با توانایی چسبیدن به همبازان و دشمنان است. او می‌تواند خود را به دو قسمت تقسیم کند و به صورت مستقل کنترل شود. با پسیو قدرتمند خود، گلو می‌تواند آسیب زیادی جذب کند و به عنوان یک محافظ عالی برای تیم عمل کند.',
        ':tips' => 'همیشه به کری‌های تیم خود بچسبید تا از آن‌ها محافظت کنید. از تقسیم شدن برای کنترل بیشتر منطقه و گیج کردن دشمنان استفاده کنید. Cursed Helmet با پسیو شما سینرژی عالی دارد.',
        ':difficulty' => 'Medium',
        ':image_url' => '/assets/images/heroes/gloo.png'
    ]);

    $heroId = (int) $db->lastInsertId();

    $skills = [
        [
            'skill_type' => 'Passive',
            'name_en' => 'Stick, Stick',
            'name_fa' => 'بچسب، بچسب',
            'description' => 'گلو می‌تواند به هیروهای متحد یا دشمن بچسبد. وقتی به متحد چسبیده، گلو 50% آسیب دریافتی آن‌ها را می‌گیرد و سرعت حرکت بونوس دریافت می‌کند. وقتی به دشمن چسبیده، گلو آسیب جادویی مداوم وارد می‌کند و سرعت حرکت آن‌ها را کاهش می‌دهد.',
            'cooldown' => null,
            'mana_cost' => null,
            'damage_type' => 'Magic',
            'tips' => 'به کری‌های تیم بچسبید تا از آن‌ها محافظت کنید. به دشمنان بچسبید برای آسیب مداوم و کاهش mobility آن‌ها.',
            'image_url' => null,
        ],
        [
            'skill_type' => 'Skill1',
            'name_en' => 'Slam, Slam',
            'name_fa' => 'کوبش، کوبش',
            'description' => 'گلو به مکان هدف می‌پرد و 250-400 (+100% قدرت جادو کل) آسیب جادویی به دشمنان در منطقه وارد می‌کند و آن‌ها را 40% برای 2 ثانیه کند می‌کند. وقتی گلو تقسیم شده است، هر دو بدن می‌توانند این مهارت را به طور مستقل استفاده کنند.',
            'cooldown' => 8.0,
            'mana_cost' => 70,
            'damage_type' => 'Magic',
            'tips' => 'از این اسکیل برای engage یا escape استفاده کنید. وقتی Split هستید، می‌توانید با هر دو بدن بپرید.',
            'image_url' => null,
        ],
        [
            'skill_type' => 'Skill2',
            'name_en' => 'Pass, Pass',
            'name_fa' => 'عبور، عبور',
            'description' => 'گلو لجن به جلو پاشیده و 300-500 (+120% قدرت جادو کل) آسیب جادویی به دشمنان وارد می‌کند و یک دنباله لجن که 3 ثانیه باقی می‌ماند باقی می‌گذارد. دشمنان روی دنباله سرعت حرکت خود را 60% کاهش می‌یابند. گلو هنگام حرکت روی دنباله خود 50% سرعت حرکت به دست می‌آورد.',
            'cooldown' => 10.0,
            'mana_cost' => 80,
            'damage_type' => 'Magic',
            'tips' => 'از دنباله لجن برای zone control استفاده کنید. روی دنباله خودتان حرکت کنید تا سریع‌تر شوید.',
            'image_url' => null,
        ],
        [
            'skill_type' => 'Ultimate',
            'name_en' => 'Split, Split / Grab, Grab',
            'name_fa' => 'تقسیم، تقسیم / گرفتن، گرفتن',
            'description' => 'گلو به دو قسمت تقسیم می‌شود که می‌توانند به طور مستقل برای 12 ثانیه کنترل شوند. گلوی کوچک می‌تواند سریع‌تر حرکت کند و مهارت‌ها را جداگانه استفاده کند. هنگام پیوستن مجدد، گلو 400-700 (+150% قدرت جادو کل) آسیب جادویی به دشمنان نزدیک وارد می‌کند و دشمنان را به مرکز می‌کشد.',
            'cooldown' => 45.0,
            'mana_cost' => 120,
            'damage_type' => 'Magic',
            'tips' => 'از Split برای گیج کردن دشمنان و کنترل بیشتر منطقه استفاده کنید. Reunite در وسط دشمنان برای Pull و آسیب.',
            'image_url' => null,
        ],
    ];

    $skillStmt = $db->prepare('INSERT INTO hero_skills (hero_id, skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url) VALUES (:hero_id, :skill_type, :name_en, :name_fa, :description, :cooldown, :mana_cost, :damage_type, :tips, :image_url)');
    foreach ($skills as $skill) {
        $skill['hero_id'] = $heroId;
        $skillStmt->execute($skill);
    }

    $skillLookupStmt = $db->prepare('SELECT id, skill_type FROM hero_skills WHERE hero_id = :hero_id');
    $skillLookupStmt->execute([':hero_id' => $heroId]);
    $skillIdsByType = [];
    while ($skillRow = $skillLookupStmt->fetch(PDO::FETCH_ASSOC)) {
        $skillIdsByType[$skillRow['skill_type']] = (int) $skillRow['id'];
    }

    $combos = [
        [
            'combo_name' => 'Protect Carry',
            'combo_name_fa' => 'محافظت از کری',
            'combo_steps' => 'Passive (Stick to Ally) → S2 (Slow Enemies) → S1 (Peel)',
            'combo_description' => 'به کری خود بچسبید تا آسیب را برای آن‌ها جذب کنید، از S2 برای کند کردن دشمنان نزدیک شونده استفاده کنید، و S1 برای دور کردن آن‌ها اگر خیلی نزدیک شدند.',
            'difficulty' => 'Easy',
        ],
        [
            'combo_name' => 'Aggressive Engage',
            'combo_name_fa' => 'حمله تهاجمی',
            'combo_steps' => 'Ult (Split) → S1 (Both bodies) → Passive (Stick to Enemy) → S2 → Reunite',
            'combo_description' => 'تقسیم شوید تا دشمنان را گیج کنید، با هر دو بدن بپرید، به هدف اولویت‌دار بچسبید، با S2 کند کنید، سپس متحد شوید برای آسیب ناگهانی و کشیدن.',
            'difficulty' => 'Hard',
        ],
        [
            'combo_name' => 'Zone Control',
            'combo_name_fa' => 'کنترل منطقه',
            'combo_steps' => 'S2 (Create Trail) → S1 → Ult (Split) → Multiple S2',
            'combo_description' => 'دنباله‌های لجن برای کنترل مناطق ایجاد کنید، از S1 برای پوک استفاده کنید، تقسیم شوید تا با دنباله‌های لجن اضافی زمین بیشتری را بپوشانید.',
            'difficulty' => 'Medium',
        ],
    ];

    $comboStmt = $db->prepare('INSERT INTO hero_combos (hero_id, combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty) VALUES (:hero_id, :combo_name, :combo_name_fa, :combo_steps, :combo_description, :combo_description_fa, :difficulty)');
    foreach ($combos as $combo) {
        $combo['hero_id'] = $heroId;
        $combo['combo_description_fa'] = $combo['combo_description'];
        $comboStmt->execute($combo);
    }

    $comboLookupStmt = $db->prepare('SELECT id, combo_name FROM hero_combos WHERE hero_id = :hero_id');
    $comboLookupStmt->execute([':hero_id' => $heroId]);
    $comboIdsByName = [];
    while ($comboRow = $comboLookupStmt->fetch(PDO::FETCH_ASSOC)) {
        $comboIdsByName[$comboRow['combo_name']] = (int) $comboRow['id'];
    }

    $comboStepsConfig = [
        'Protect Carry' => ['Passive', 'Skill2', 'Skill1'],
        'Aggressive Engage' => ['Ultimate', 'Skill1', 'Passive', 'Skill2', 'Ultimate'],
        'Zone Control' => ['Skill2', 'Skill1', 'Ultimate', 'Skill2'],
    ];

    $comboStepStmt = $db->prepare('INSERT INTO hero_combo_steps (combo_id, skill_id, sort_order) VALUES (:combo_id, :skill_id, :sort_order)');
    foreach ($comboStepsConfig as $comboName => $stepTypes) {
        if (!isset($comboIdsByName[$comboName])) {
            continue;
        }

        $comboId = $comboIdsByName[$comboName];
        $sortOrder = 1;

        foreach ($stepTypes as $type) {
            if (!isset($skillIdsByType[$type])) {
                continue;
            }

            $comboStepStmt->execute([
                ':combo_id' => $comboId,
                ':skill_id' => $skillIdsByType[$type],
                ':sort_order' => $sortOrder++,
            ]);
        }
    }

    $tips = [
        ['tip_type' => 'General', 'tip_text' => 'همیشه به MM یا Mage تیم خود بچسبید در تیم فایت‌ها - می‌توانید 50% آسیب آن‌ها را جذب کنید', 'priority' => 10],
        ['tip_type' => 'General', 'tip_text' => 'وقتی Split می‌کنید، بدن کوچک را برای Poke استفاده کنید در حالی که بدن بزرگ Front line را نگه می‌دارد', 'priority' => 9],
        ['tip_type' => 'Combat', 'tip_text' => 'S2 شما یک Zone tool عالی است - از آن برای کنترل چوک پوینت‌ها استفاده کنید', 'priority' => 8],
        ['tip_type' => 'Combat', 'tip_text' => 'هنگام Reunite، می‌توانید دشمنان را Pull کنید - این را با Ultimate تیم خود Combo کنید', 'priority' => 9],
        ['tip_type' => 'Strategy', 'tip_text' => 'در Early Game، به Jungler بچسبید تا Ganks را قوی‌تر کنید', 'priority' => 7],
        ['tip_type' => 'Strategy', 'tip_text' => 'Cursed Helmet با پسیو شما سینرژی عالی دارد - وقتی به دشمن چسبیده‌اید آسیب مداوم می‌دهد', 'priority' => 8],
        ['tip_type' => 'Advanced', 'tip_text' => 'می‌توانید از Split برای فرار استفاده کنید - بدن کوچک را برای Bait کردن استفاده کنید', 'priority' => 6],
        ['tip_type' => 'Advanced', 'tip_text' => 'Timing Reunite برای Pull دشمنان به Tower یا Team را یاد بگیرید', 'priority' => 7],
    ];

    $tipStmt = $db->prepare('INSERT INTO hero_tips (hero_id, tip_type, tip_text, tip_text_en, priority) VALUES (:hero_id, :tip_type, :tip_text, :tip_text_en, :priority)');
    foreach ($tips as $tip) {
        $tip['hero_id'] = $heroId;
        $tip['tip_text_en'] = $tip['tip_text'];
        $tipStmt->execute($tip);
    }

    $counterGroups = [
        [
            'group_type' => 'hero',
            'title_en' => 'Gloo is Weak Against',
            'title_fa' => 'گلو در برابر این هیروها ضعیف است',
            'description_en' => 'Heroes with tools to punish Gloo throughout the match.',
            'description_fa' => 'این هیروها در اکثر فازهای بازی در برابر گلو برتری دارند.',
            'sort_order' => 1,
            'entities' => [
                ['name_en' => 'Yu Zhong', 'name_fa' => 'یو ژونگ'],
                ['name_en' => 'Lancelot', 'name_fa' => 'لنسلوت'],
                ['name_en' => 'Faramis', 'name_fa' => 'فارامیس'],
                ['name_en' => 'Vexana', 'name_fa' => 'وکسنا'],
                ['name_en' => 'Wanwan', 'name_fa' => 'وان‌وان'],
            ],
        ],
        [
            'group_type' => 'hero',
            'title_en' => 'Gloo is Strong Against',
            'title_fa' => 'گلو در برابر این هیروها قدرتمند است',
            'description_en' => "Targets that struggle against Gloo's crowd control.",
            'description_fa' => 'این هیروها در برابر کنترل و دوام گلو مشکل دارند.',
            'sort_order' => 2,
            'entities' => [
                ['name_en' => 'Barats', 'name_fa' => 'باراتس'],
                ['name_en' => 'Khaleed', 'name_fa' => 'خالد'],
                ['name_en' => 'Popol and Kupa', 'name_fa' => 'پوپل و کوپا'],
                ['name_en' => 'Yve', 'name_fa' => 'ایو'],
                ['name_en' => 'Benedetta', 'name_fa' => 'بندتا'],
            ],
        ],
        [
            'group_type' => 'item',
            'title_en' => 'Items to Cut Regen',
            'title_fa' => 'آیتم‌های ضد ریجن',
            'description_en' => "Pick these to reduce Gloo's regeneration and sustain.",
            'description_fa' => 'برای کاهش ریجنریشن گلو از این آیتم‌ها استفاده کنید.',
            'sort_order' => 3,
            'entities' => [
                ['item_slug' => 'sea-halberd'],
                ['item_slug' => 'glowing-wand'],
                ['item_slug' => 'dominance-ice'],
            ],
        ],
        [
            'group_type' => 'item',
            'title_en' => 'Items to Pierce Defense',
            'title_fa' => 'آیتم‌های نفوذ دفاع',
            'description_en' => 'Armor and magic penetration options to break Gloo down.',
            'description_fa' => 'برای کاهش دفاع گلو از این آیتم‌ها بهره ببرید.',
            'sort_order' => 4,
            'entities' => [
                ['item_slug' => 'malefic-roar'],
                ['item_slug' => 'genius-wand'],
                ['item_slug' => 'divine-glaive'],
            ],
        ],
        [
            'group_type' => 'item',
            'title_en' => 'Percent HP Damage Items',
            'title_fa' => 'آیتم‌های با درصد آسیب HP',
            'description_en' => 'Items that deal damage based on maximum HP to counter tanks.',
            'description_fa' => 'این آیتم‌ها با درصدی از جان هدف آسیب می‌زنند و برای مقابله با تانک‌ها عالی هستند.',
            'sort_order' => 5,
            'entities' => [
                ['item_slug' => 'demon-hunter-sword'],
                ['item_slug' => 'wishing-lantern'],
            ],
        ],
    ];

    $groupStmt = $db->prepare('INSERT INTO hero_counter_groups (hero_id, group_type, title_en, title_fa, description_en, description_fa, sort_order) VALUES (:hero_id, :group_type, :title_en, :title_fa, :description_en, :description_fa, :sort_order)');
    $entityStmt = $db->prepare('INSERT INTO hero_counter_entities (group_id, target_hero_id, target_item_id, name_en, name_fa, image_url, sort_order) VALUES (:group_id, :target_hero_id, :target_item_id, :name_en, :name_fa, :image_url, :sort_order)');
    $itemDetailStmt = $db->prepare('SELECT name_en, name_fa, image_url FROM items WHERE id = :id');

    foreach ($counterGroups as $group) {
        $entities = $group['entities'];
        unset($group['entities']);

        $group['hero_id'] = $heroId;
        $groupStmt->execute($group);
        $groupId = (int) $db->lastInsertId();

        $order = 1;
        foreach ($entities as $entity) {
            $targetItemId = null;
            $targetHeroId = null;
            $nameEn = $entity['name_en'] ?? null;
            $nameFa = $entity['name_fa'] ?? null;
            $imageUrl = $entity['image_url'] ?? null;

            if (!empty($entity['item_slug'])) {
                $slug = $entity['item_slug'];
                $targetItemId = $itemIds[$slug] ?? null;

                if ($targetItemId !== null) {
                    $itemDetailStmt->execute([':id' => $targetItemId]);
                    $itemRow = $itemDetailStmt->fetch(PDO::FETCH_ASSOC);
                    if ($itemRow) {
                        $nameEn = $itemRow['name_en'];
                        $nameFa = $itemRow['name_fa'];
                        $imageUrl = $imageUrl ?? ($itemRow['image_url'] ?? null);
                    }
                }
            }

            $entityStmt->execute([
                ':group_id' => $groupId,
                ':target_hero_id' => $targetHeroId,
                ':target_item_id' => $targetItemId,
                ':name_en' => $nameEn,
                ':name_fa' => $nameFa,
                ':image_url' => $imageUrl,
                ':sort_order' => $order++,
            ]);
        }
    }

    $strengths = [
        'توانایی منحصر به فرد چسبیدن به متحدان برای محافظت - 50% آسیب را جذب می‌کند',
        'Ultimate Split برای Zone Control و Engage عالی است',
        'ریجنریشن خوب با بیلد مناسب',
        'می‌تواند به عنوان Tank/Support Hybrid عمل کند',
        'توانایی Pull دشمنان با Reunite',
        'Slow قوی با S2 برای Peel یا Engage',
        'تانک بودن و آسیب مداوم با Cursed Helmet',
    ];

    $strengthStmt = $db->prepare('INSERT INTO hero_strengths (hero_id, strength_text) VALUES (:hero_id, :strength_text)');
    foreach ($strengths as $strength) {
        $strengthStmt->execute([
            ':hero_id' => $heroId,
            ':strength_text' => $strength,
        ]);
    }

    $weaknesses = [
        'مکانیک Split پیچیده است و نیاز به تمرین دارد',
        'بدون Ultimate، قدرت Engage پایینی دارد',
        'آسیب‌پذیر در برابر Anti-regen (Baxia, Sea Halberd)',
        'CC Immunity دشمنان (Yu Zhong Ultimate) Passive را بی‌اثر می‌کند',
        'Mobility پایین بدون Stick Passive',
        'در Late Game scaling ضعیف‌تر از سایر Tank ها',
        'Diggie او را به طور کامل Counter می‌کند با CC Immunity',
    ];

    $weaknessStmt = $db->prepare('INSERT INTO hero_weaknesses (hero_id, weakness_text) VALUES (:hero_id, :weakness_text)');
    foreach ($weaknesses as $weakness) {
        $weaknessStmt->execute([
            ':hero_id' => $heroId,
            ':weakness_text' => $weakness,
        ]);
    }

    $db->commit();

    echo "✅ هیرو Gloo اضافه شد (ID: {$heroId})" . PHP_EOL;
    echo "💡 برای تست: /api/hero_detail.php?id={$heroId}" . PHP_EOL;
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    echo '❌ خطا در درج اطلاعات: ' . $e->getMessage() . PHP_EOL;
    echo '📍 Line: ' . $e->getLine() . PHP_EOL;
    exit(1);
}
