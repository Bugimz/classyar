<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $db->beginTransaction();

    $heroQuery = "INSERT INTO heroes (name_en, name_fa, role_id, specialty, hp, hp_regen, mana, mana_regen, physical_attack, magic_power, physical_defense, magic_defense, attack_speed, movement_speed, description, tips, difficulty, image_url)
        VALUES (:name_en, :name_fa, :role_id, :specialty, :hp, :hp_regen, :mana, :mana_regen, :physical_attack, :magic_power, :physical_defense, :magic_defense, :attack_speed, :movement_speed, :description, :tips, :difficulty, :image_url)";

    $heroStmt = $db->prepare($heroQuery);
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

    $skillQuery = "INSERT INTO hero_skills (hero_id, skill_type, name_en, name_fa, description, cooldown, mana_cost, damage_type, tips, image_url)
        VALUES (:hero_id, :skill_type, :name_en, :name_fa, :description, :cooldown, :mana_cost, :damage_type, :tips, :image_url)";

    $skillStmt = $db->prepare($skillQuery);
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

    $comboQuery = "INSERT INTO hero_combos (hero_id, combo_name, combo_name_fa, combo_steps, combo_description, combo_description_fa, difficulty)
        VALUES (:hero_id, :combo_name, :combo_name_fa, :combo_steps, :combo_description, :combo_description_fa, :difficulty)";

    $comboStmt = $db->prepare($comboQuery);
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

    $tipQuery = "INSERT INTO hero_tips (hero_id, tip_type, tip_text, tip_text_en, priority)
        VALUES (:hero_id, :tip_type, :tip_text, :tip_text_en, :priority)";

    $tipStmt = $db->prepare($tipQuery);
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
                ['name_en' => 'Benedetta', 'name_fa' => 'بنِدتا'],
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
                ['name_en' => 'Sea Halberd', 'name_fa' => 'سی هالبرد'],
                ['name_en' => 'Glowing Wand', 'name_fa' => 'گلوینگ وند'],
                ['name_en' => 'Dominance Ice', 'name_fa' => 'دومیننس آیس'],
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
                ['name_en' => 'Malefic Roar', 'name_fa' => 'میلفیک روار'],
                ['name_en' => 'Genius Wand', 'name_fa' => 'جینیوس وند'],
                ['name_en' => 'Divine Glaive', 'name_fa' => 'دی‌واین گلیو'],
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
                ['name_en' => 'Demon Hunter Sword', 'name_fa' => 'دمون هانتر سورد'],
                ['name_en' => 'Wishing Lantern', 'name_fa' => 'ویشینگ لنترن'],
            ],
        ],
    ];

    $groupStmt = $db->prepare('INSERT INTO hero_counter_groups (hero_id, group_type, title_en, title_fa, description_en, description_fa, sort_order)
        VALUES (:hero_id, :group_type, :title_en, :title_fa, :description_en, :description_fa, :sort_order)');

    $entityStmt = $db->prepare('INSERT INTO hero_counter_entities (group_id, name_en, name_fa, image_url, sort_order)
        VALUES (:group_id, :name_en, :name_fa, :image_url, :sort_order)');

    foreach ($counterGroups as $group) {
        $groupData = $group;
        $entities = $groupData['entities'];
        unset($groupData['entities']);

        $groupData['hero_id'] = $heroId;
        $groupStmt->execute($groupData);

        $groupId = (int) $db->lastInsertId();
        $order = 1;
        foreach ($entities as $entity) {
            $entityStmt->execute([
                ':group_id' => $groupId,
                ':name_en' => $entity['name_en'],
                ':name_fa' => $entity['name_fa'],
                ':image_url' => null,
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

    $strengthQuery = "INSERT INTO hero_strengths (hero_id, strength_text) VALUES (:hero_id, :strength_text)";
    $strengthStmt = $db->prepare($strengthQuery);
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

    $weaknessQuery = "INSERT INTO hero_weaknesses (hero_id, weakness_text) VALUES (:hero_id, :weakness_text)";
    $weaknessStmt = $db->prepare($weaknessQuery);
    foreach ($weaknesses as $weakness) {
        $weaknessStmt->execute([
            ':hero_id' => $heroId,
            ':weakness_text' => $weakness,
        ]);
    }

    $db->commit();

    echo "✅ هیرو Gloo اضافه شد (ID: {$heroId})" . PHP_EOL;
    echo "💡 برای تست: /api/hero_detail.php?id={$heroId}" . PHP_EOL;
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo '❌ خطا در درج اطلاعات: ' . $e->getMessage() . PHP_EOL;
    echo '📍 Line: ' . $e->getLine() . PHP_EOL;
    exit(1);
}
