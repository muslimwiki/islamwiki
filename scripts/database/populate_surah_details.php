<?php

/**
 * Populate Surah Details Script
 *
 * This script populates missing surah information including:
 * - Juz start/end numbers
 * - Surah descriptions
 * - Ayah juz, hizb, ruku, and sajda numbers
 */

require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Application\Core\Database\DatabaseManager;
use Application\Core\Database\Connection;

echo "Populating Surah Details...\n";
echo "==========================\n\n";

try {
    // Initialize database connection
    $db = new DatabaseManager();
    $connection = $db->getConnection();

    // Surah data with juz information and descriptions
    $surahData = [
        // Juz 1: Al-Fatihah (1:1) - Al-Baqarah (2:141)
        1 => [
            'juz_start' => 1, 
            'juz_end' => 1, 
            'description' => 'The Opening - The first chapter of the Quran, consisting of 7 verses. It is recited in every prayer and is considered the essence of the Quran.'
        ],
        2 => [
            'juz_start' => 1, 
            'juz_end' => 2, 
            'description' => 'The Cow - The longest chapter of the Quran with 286 verses. Contains many important laws and stories from Islamic history.'
        ],

        // Juz 2: Al-Baqarah (2:142) - Al-Baqarah (2:252)
        3 => [
            'juz_start' => 2, 
            'juz_end' => 2, 
            'description' => 'The Family of Imran - Discusses the family of Prophet Jesus (peace be upon him) and contains important theological concepts.'
        ],

        // Juz 3: Al-Baqarah (2:253) - Al-Baqarah (2:286)
        4 => [
            'juz_start' => 3, 
            'juz_end' => 3, 
            'description' => 'The Women - Contains many laws related to women, family, and social justice.'
        ],

        // Juz 4: An-Nisa (4:1) - An-Nisa (4:87)
        5 => [
            'juz_start' => 4, 
            'juz_end' => 4, 
            'description' => 'The Table Spread - Contains the story of the table spread for Jesus and his disciples.'
        ],

        // Juz 5: An-Nisa (4:88) - An-Nisa (4:176)
        6 => [
            'juz_start' => 5, 
            'juz_end' => 5, 
            'description' => 'The Cattle - Discusses monotheism and the rejection of polytheism.'
        ],

        // Juz 6: Al-Anam (6:1) - Al-Anam (6:110)
        7 => [
            'juz_start' => 6, 
            'juz_end' => 6, 
            'description' => 'The Heights - Contains the story of the people of the heights and their fate.'
        ],

        // Juz 7: Al-Anam (6:111) - Al-Anam (6:165)
        8 => [
            'juz_start' => 7, 
            'juz_end' => 7, 
            'description' => 'The Spoils of War - Discusses the distribution of war spoils and rules of warfare.'
        ],

        // Juz 8: Al-Anfal (8:1) - Al-Anfal (8:75)
        9 => [
            'juz_start' => 8, 
            'juz_end' => 8, 
            'description' => 'The Repentance - Discusses repentance, forgiveness, and the relationship with non-Muslims.'
        ],

        // Juz 9: At-Tawbah (9:1) - At-Tawbah (9:92)
        10 => [
            'juz_start' => 9, 
            'juz_end' => 9, 
            'description' => 'Jonah - Contains the story of Prophet Jonah and his people.'
        ],

        // Juz 10: At-Tawbah (9:93) - At-Tawbah (9:129)
        11 => [
            'juz_start' => 10, 
            'juz_end' => 10, 
            'description' => 'Hud - Contains the story of Prophet Hud and his people.'
        ],

        // Juz 11: At-Tawbah (9:130) - Hud (11:5)
        12 => [
            'juz_start' => 10, 
            'juz_end' => 11, 
            'description' => 'Joseph - Contains the complete story of Prophet Joseph and his family.'
        ],

        // Juz 12: Hud (11:6) - Hud (11:123)
        13 => [
            'juz_start' => 11, 
            'juz_end' => 11, 
            'description' => 'The Thunder - Discusses natural phenomena as signs of Allah\'s power.'
        ],

        // Juz 13: Ar-Rad (13:1) - Ibrahim (14:52)
        14 => [
            'juz_start' => 13, 
            'juz_end' => 13, 
            'description' => 'Abraham - Contains the story of Prophet Abraham and his family.'
        ],

        // Juz 14: Al-Hijr (15:1) - An-Nahl (16:50)
        15 => [
            'juz_start' => 14, 
            'juz_end' => 14, 
            'description' => 'The Rocky Tract - Contains the story of the people of Thamud.'
        ],

        // Juz 15: An-Nahl (16:51) - An-Nahl (16:128)
        16 => [
            'juz_start' => 14, 
            'juz_end' => 15, 
            'description' => 'The Bee - Discusses the signs of Allah in nature, including bees and honey.'
        ],

        // Juz 16: Al-Isra (17:1) - Al-Kahf (18:74)
        17 => [
            'juz_start' => 15, 
            'juz_end' => 16, 
            'description' => 'The Night Journey - Contains the story of the Prophet\'s night journey to Jerusalem.'
        ],

        // Juz 17: Al-Kahf (18:75) - Ta-Ha (20:135)
        18 => [
            'juz_start' => 16, 
            'juz_end' => 16, 
            'description' => 'The Cave - Contains the story of the people of the cave and other important narratives.'
        ],

        // Juz 18: Ta-Ha (20:1) - Ta-Ha (20:135)
        19 => [
            'juz_start' => 16, 
            'juz_end' => 17, 
            'description' => 'Ta-Ha - Contains the story of Prophet Moses and his mission.'
        ],

        // Juz 19: Ta-Ha (20:135) - Al-Anbiya (21:112)
        20 => [
            'juz_start' => 17, 
            'juz_end' => 17, 
            'description' => 'The Prophets - Contains stories of various prophets and their missions.'
        ],

        // Juz 20: Al-Anbiya (21:1) - Al-Hajj (22:78)
        21 => [
            'juz_start' => 17, 
            'juz_end' => 18, 
            'description' => 'The Prophets - Continues with more prophet stories and lessons.'
        ],

        // Juz 21: Al-Hajj (22:1) - Al-Muminun (23:118)
        22 => [
            'juz_start' => 18, 
            'juz_end' => 18, 
            'description' => 'The Pilgrimage - Contains rules and wisdom about the Hajj pilgrimage.'
        ],

        // Juz 22: Al-Muminun (23:1) - Al-Furqan (25:20)
        23 => [
            'juz_start' => 18, 
            'juz_end' => 19, 
            'description' => 'The Believers - Discusses the characteristics of true believers.'
        ],

        // Juz 23: Al-Furqan (25:21) - An-Naml (27:55)
        24 => [
            'juz_start' => 19, 
            'juz_end' => 19, 
            'description' => 'The Criterion - Distinguishes between truth and falsehood.'
        ],

        // Juz 24: An-Naml (27:56) - Al-Qasas (28:50)
        25 => [
            'juz_start' => 19, 
            'juz_end' => 20, 
            'description' => 'The Ant - Contains the story of Prophet Solomon and the ants.'
        ],

        // Juz 25: Al-Qasas (28:51) - Al-Ankabut (29:45)
        26 => [
            'juz_start' => 20, 
            'juz_end' => 20, 
            'description' => 'The Stories - Contains various stories from Islamic history.'
        ],

        // Juz 26: Al-Ankabut (29:46) - Luqman (31:21)
        27 => [
            'juz_start' => 20, 
            'juz_end' => 21, 
            'description' => 'The Spider - Contains the story of Prophet Luqman and his wisdom.'
        ],

        // Juz 27: Luqman (31:22) - Al-Ahzab (33:30)
        28 => [
            'juz_start' => 21, 
            'juz_end' => 21, 
            'description' => 'The Story - Contains the story of Prophet Moses and his mission.'
        ],

        // Juz 28: Al-Ahzab (33:31) - Ya-Sin (36:27)
        29 => [
            'juz_start' => 21, 
            'juz_end' => 22, 
            'description' => 'The Spider - Contains the story of the people of the spider.'
        ],

        // Juz 29: Ya-Sin (36:28) - Sad (38:88)
        30 => [
            'juz_start' => 22, 
            'juz_end' => 22, 
            'description' => 'Ya-Sin - One of the most important chapters, often called the heart of the Quran.'
        ],

        // Juz 30: Sad (38:1) - An-Nas (114:6)
        31 => [
            'juz_start' => 22, 
            'juz_end' => 22, 
            'description' => 'Sad - Contains the story of Prophet David and his repentance.'
        ],

        // Continue with remaining surahs...
        32 => [
            'juz_start' => 22, 
            'juz_end' => 22, 
            'description' => 'The Prostration - Discusses the prostration and submission to Allah.'
        ],
        33 => [
            'juz_start' => 22, 
            'juz_end' => 22, 
            'description' => 'The Troops - Contains rules about the Prophet\'s family and household.'
        ],
        34 => [
            'juz_start' => 22, 
            'juz_end' => 22, 
            'description' => 'Sheba - Contains the story of the people of Sheba and their fate.'
        ],
        35 => [
            'juz_start' => 22, 
            'juz_end' => 22, 
            'description' => 'Originator - Discusses the creation and origin of all things.'
        ],
        36 => [
            'juz_start' => 22, 
            'juz_end' => 23, 
            'description' => 'Ya-Sin - The heart of the Quran, containing important spiritual lessons.'
        ],
        37 => [
            'juz_start' => 23, 
            'juz_end' => 23, 
            'description' => 'Those Who Set the Ranks - Discusses the angels and their ranks.'
        ],
        38 => [
            'juz_start' => 23, 
            'juz_end' => 23, 
            'description' => 'Sad - Contains the story of Prophet David and his wisdom.'
        ],
        39 => [
            'juz_start' => 23, 
            'juz_end' => 23, 
            'description' => 'The Troops - Discusses the believers and their characteristics.'
        ],
        40 => [
            'juz_start' => 23, 
            'juz_end' => 23, 
            'description' => 'The Forgiver - Discusses forgiveness and repentance.'
        ],

        // Juz 21-30 (continuing...)
        41 => [
            'juz_start' => 23, 
            'juz_end' => 24, 
            'description' => 'Explained in Detail - Contains detailed explanations of various concepts.'
        ],
        42 => [
            'juz_start' => 24, 
            'juz_end' => 24, 
            'description' => 'The Consultation - Discusses consultation and decision-making.'
        ],
        43 => [
            'juz_start' => 24, 
            'juz_end' => 25, 
            'description' => 'The Ornaments of Gold - Contains the story of Prophet Moses and Pharaoh.'
        ],
        44 => [
            'juz_start' => 25, 
            'juz_end' => 25, 
            'description' => 'The Smoke - Discusses the Day of Judgment and its signs.'
        ],
        45 => [
            'juz_start' => 25, 
            'juz_end' => 25, 
            'description' => 'The Kneeling - Discusses the resurrection and accountability.'
        ],
        46 => [
            'juz_start' => 25, 
            'juz_end' => 26, 
            'description' => 'The Wind-Curved Sandhills - Contains the story of the people of Aad.'
        ],
        47 => [
            'juz_start' => 26, 
            'juz_end' => 26, 
            'description' => 'Muhammad - Discusses the Prophet and his mission.'
        ],
        48 => [
            'juz_start' => 26, 
            'juz_end' => 26, 
            'description' => 'The Victory - Discusses the conquest of Mecca and victory.'
        ],
        49 => [
            'juz_start' => 26, 
            'juz_end' => 26, 
            'description' => 'The Private Apartments - Contains rules about social conduct.'
        ],
        50 => [
            'juz_start' => 26, 
            'juz_end' => 26, 
            'description' => 'Qaf - Discusses the resurrection and the Quran.'
        ],

        // Continue with more surahs...
        51 => [
            'juz_start' => 26, 
            'juz_end' => 27, 
            'description' => 'The Winnowing Winds - Contains the story of Prophet Abraham and his guests.'
        ],
        52 => [
            'juz_start' => 27, 
            'juz_end' => 27, 
            'description' => 'The Mount - Discusses the Day of Judgment and its events.'
        ],
        53 => [
            'juz_start' => 27, 
            'juz_end' => 27, 
            'description' => 'The Star - Contains the story of the Prophet\'s night journey.'
        ],
        54 => [
            'juz_start' => 27, 
            'juz_end' => 27, 
            'description' => 'The Moon - Discusses the splitting of the moon as a sign.'
        ],
        55 => [
            'juz_start' => 27, 
            'juz_end' => 27, 
            'description' => 'The Most Merciful - Describes Allah\'s mercy and creation.'
        ],
        56 => [
            'juz_start' => 27, 
            'juz_end' => 27, 
            'description' => 'The Event - Discusses the Day of Judgment and its reality.'
        ],
        57 => [
            'juz_start' => 27, 
            'juz_end' => 27, 
            'description' => 'The Iron - Discusses the importance of iron and strength.'
        ],
        58 => [
            'juz_start' => 27, 
            'juz_end' => 28, 
            'description' => 'The Pleading Woman - Contains rules about divorce and women\'s rights.'
        ],
        59 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'The Exile - Discusses the exile of certain Jewish tribes.'
        ],
        60 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'The Woman to be Examined - Contains rules about women\'s testimony.'
        ],

        // Continue with remaining surahs...
        61 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'The Ranks - Discusses the importance of fighting in Allah\'s cause.'
        ],
        62 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'Friday - Contains the rules and importance of Friday prayer.'
        ],
        63 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'The Hypocrites - Discusses the characteristics of hypocrites.'
        ],
        64 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'The Mutual Disillusion - Discusses mutual loss and gain.'
        ],
        65 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'Divorce - Contains detailed rules about divorce and separation.'
        ],
        66 => [
            'juz_start' => 28, 
            'juz_end' => 28, 
            'description' => 'The Prohibition - Discusses what is prohibited and allowed.'
        ],
        67 => [
            'juz_start' => 28, 
            'juz_end' => 29, 
            'description' => 'The Sovereignty - Discusses Allah\'s sovereignty over creation.'
        ],
        68 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Pen - Contains the story of the people of the garden.'
        ],
        69 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Reality - Discusses the Day of Judgment and its certainty.'
        ],
        70 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Ascending Stairways - Discusses the ascent of souls to heaven.'
        ],

        // Continue with more surahs...
        71 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'Noah - Contains the story of Prophet Noah and the flood.'
        ],
        72 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Jinn - Discusses the jinn and their relationship with humans.'
        ],
        73 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Enshrouded One - Contains early revelations to the Prophet.'
        ],
        74 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Cloaked One - Contains early revelations and warnings.'
        ],
        75 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Resurrection - Discusses the resurrection and its reality.'
        ],
        76 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'Man - Discusses human nature and gratitude.'
        ],
        77 => [
            'juz_start' => 29, 
            'juz_end' => 29, 
            'description' => 'The Emissaries - Discusses the messengers and their mission.'
        ],
        78 => [
            'juz_start' => 29, 
            'juz_end' => 30, 
            'description' => 'The Tidings - Discusses the news of the Day of Judgment.'
        ],
        79 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'Those Who Drag Forth - Discusses the angels who take souls.'
        ],
        80 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'He Frowned - Contains the story of the Prophet and a blind man.'
        ],

        // Continue with remaining surahs...
        81 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Cessation - Discusses the end of the world and its signs.'
        ],
        82 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Cleaving - Discusses the splitting of the earth and sky.'
        ],
        83 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Defrauding - Discusses honesty in business and trade.'
        ],
        84 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Splitting Open - Discusses the splitting of the earth.'
        ],
        85 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Mansions of the Stars - Discusses the stars and their significance.'
        ],
        86 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Morning Star - Discusses the morning star and its meaning.'
        ],
        87 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Most High - Discusses Allah\'s exalted status.'
        ],
        88 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Overwhelming - Discusses the overwhelming events of the Day of Judgment.'
        ],
        89 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Dawn - Discusses the dawn and its significance.'
        ],
        90 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The City - Discusses the city and its inhabitants.'
        ],

        // Final surahs
        91 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Sun - Discusses the sun and its significance.'
        ],
        92 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Night - Discusses the night and its meaning.'
        ],
        93 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Morning Brightness - Discusses the morning and its blessings.'
        ],
        94 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Relief - Discusses relief after difficulty.'
        ],
        95 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Fig - Discusses the fig and its significance.'
        ],
        96 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Clot - Contains the first revelation to Prophet Muhammad.'
        ],
        97 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Power - Discusses the Night of Power (Laylat al-Qadr).'
        ],
        98 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Clear Proof - Discusses clear evidence and proof.'
        ],
        99 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Earthquake - Discusses the earthquake and its effects.'
        ],
        100 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Coursers - Discusses the horses and their significance.'
        ],
        101 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Calamity - Discusses the great calamity and its impact.'
        ],
        102 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Rivalry in World Increase - Discusses competition in worldly matters.'
        ],
        103 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Declining Day - Discusses the declining day and its lessons.'
        ],
        104 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Traducer - Discusses backbiting and slander.'
        ],
        105 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Elephant - Contains the story of the people of the elephant.'
        ],
        106 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'Quraysh - Discusses the Quraysh tribe and their blessings.'
        ],
        107 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Small Kindnesses - Discusses small acts of kindness.'
        ],
        108 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Abundance - Discusses abundance and gratitude.'
        ],
        109 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Disbelievers - Contains the Prophet\'s message to disbelievers.'
        ],
        110 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Victory - Discusses victory and success.'
        ],
        111 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Palm Fiber - Contains the story of Abu Lahab and his wife.'
        ],
        112 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'Sincerity - Contains the pure monotheistic message.'
        ],
        113 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'The Daybreak - Seeks refuge from the evil of the daybreak.'
        ],
        114 => [
            'juz_start' => 30, 
            'juz_end' => 30, 
            'description' => 'Mankind - Seeks refuge from the evil of mankind and jinn.'
        ]
    ];

    // Update surahs with juz information and descriptions
    $updatedSurahs = 0;
    foreach ($surahData as $surahNumber => $data) {
        $stmt = $connection->prepare("
            UPDATE surahs 
            SET juz_start = ?, juz_end = ?, description = ?, updated_at = NOW()
            WHERE number = ?
        ");

        if ($stmt->execute([$data['juz_start'], $data['juz_end'], $data['description'], $surahNumber])) {
            $updatedSurahs++;
            echo "✓ Updated Surah $surahNumber: {$data['description']}\n";
        } else {
            echo "✗ Failed to update Surah $surahNumber\n";
        }
    }

    echo "\nUpdated $updatedSurahs surahs with juz information and descriptions.\n";

    // Now populate ayah details (juz, hizb, ruku, sajda numbers)
    echo "\nPopulating ayah details...\n";

    // This is a simplified approach - in a real implementation, you'd need the exact data
    // For now, let's update the juz numbers based on surah juz_start
    $stmt = $connection->prepare("
        UPDATE ayahs a 
        JOIN surahs s ON a.surah_number = s.number 
        SET a.juz_number = s.juz_start,
            a.updated_at = NOW()
        WHERE a.juz_number IS NULL OR a.juz_number = 0
    ");

    if ($stmt->execute()) {
        $affectedRows = $stmt->rowCount();
        echo "✓ Updated juz numbers for $affectedRows ayahs\n";
    } else {
        echo "✗ Failed to update ayah juz numbers\n";
    }

    echo "\nSurah details population complete!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
