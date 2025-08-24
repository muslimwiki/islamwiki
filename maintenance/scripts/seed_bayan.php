<?php

declare(strict_types=1);

// Seed Bayan knowledge graph with a few useful nodes and relationships

function db(): PDO {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $db   = getenv('DB_DATABASE') ?: 'islamwiki';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';
    $dsn  = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    $pdo  = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    return $pdo;
}

function getNodeId(PDO $pdo, string $type, string $title): ?int {
    $stmt = $pdo->prepare("SELECT id FROM bayan_nodes WHERE type = ? AND title = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$type, $title]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['id'] : null;
}

function getOrCreateNode(PDO $pdo, string $type, string $title, string $content = '', array $metadata = []): int {
    $id = getNodeId($pdo, $type, $title);
    if ($id) return $id;

    $stmt = $pdo->prepare("INSERT INTO bayan_nodes (type, title, content, metadata, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$type, $title, $content, json_encode($metadata, JSON_UNESCAPED_UNICODE)]);
    return (int)$pdo->lastInsertId();
}

function edgeExists(PDO $pdo, int $sourceId, int $targetId, string $type): bool {
    $stmt = $pdo->prepare("SELECT id FROM bayan_edges WHERE source_id = ? AND target_id = ? AND type = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$sourceId, $targetId, $type]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

function createEdge(PDO $pdo, int $sourceId, int $targetId, string $type, array $attributes = []): ?int {
    if (edgeExists($pdo, $sourceId, $targetId, $type)) return null;
    $stmt = $pdo->prepare("INSERT INTO bayan_edges (source_id, target_id, type, attributes, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$sourceId, $targetId, $type, json_encode($attributes, JSON_UNESCAPED_UNICODE)]);
    return (int)$pdo->lastInsertId();
}

function main(): void {
    $pdo = db();

    $nodes = [
        ['concept', 'Fitrah (Primordial Nature)', 'Human primordial disposition towards truth.'],
        ['concept', 'Faith (Faith)', 'Faith consisting of belief in the heart, statement by the tongue, action by limbs.'],
        ['concept', 'Islam (Submission)', 'Submission to Allah with Tawhid, yielding to Him in obedience.'],
        ['concept', 'Kufr (Disbelief)', 'Covering/rejecting truth; opposite of Faith.'],
        ['ayah',  "Qur'an 30:30", 'So set your face toward the religion, inclining to truth. Adhere to the fitrah of Allah...'],
        ['hadith', 'Religion is sincere advice', 'Ad-din an-nasihah. We said: To whom? He said: To Allah, His Book, His Messenger...'],
        ['scholar', 'Imam al-Nawawi', 'Muhyiddin Yahya ibn Sharaf an-Nawawi (631–676 AH).'],
        ['book', 'Riyadh al-Salihin', 'A compilation by Imam al-Nawawi of authentic hadith on ethics and worship.'],
    ];

    $titleToId = [];
    foreach ($nodes as [$type, $title, $content]) {
        $id = getOrCreateNode($pdo, $type, $title, $content);
        $titleToId[$title] = $id;
        echo "Node: {$type} :: {$title} => {$id}\n";
    }

    // Relationships
    $rels = [
        // source, target, type
        ['Kufr (Disbelief)', 'Faith (Faith)', 'opposes'],
        ['Fitrah (Primordial Nature)', 'Faith (Faith)', 'supports'],
        ["Fitrah (Primordial Nature)", "Qur'an 30:30", 'explains'],
        ['Riyadh al-Salihin', 'Imam al-Nawawi', 'authored_by'],
        ['Riyadh al-Salihin', 'Religion is sincere advice', 'references'],
        ['Islam (Submission)', 'Faith (Faith)', 'related_to'],
    ];

    foreach ($rels as [$srcTitle, $tgtTitle, $type]) {
        $src = $titleToId[$srcTitle] ?? getNodeId($pdo, '%', $srcTitle); // fallback
        $tgt = $titleToId[$tgtTitle] ?? getNodeId($pdo, '%', $tgtTitle);
        if (!$src || !$tgt) {
            echo "Skip edge {$type}: missing node(s) {$srcTitle} / {$tgtTitle}\n";
            continue;
        }
        $edgeId = createEdge($pdo, $src, $tgt, $type);
        echo "Edge: {$srcTitle} -[{$type}]-> {$tgtTitle} => " . ($edgeId ? $edgeId : 'exists') . "\n";
    }

    // Summary counts
    $countNodes = (int)$pdo->query("SELECT COUNT(*) FROM bayan_nodes WHERE deleted_at IS NULL")->fetchColumn();
    $countEdges = (int)$pdo->query("SELECT COUNT(*) FROM bayan_edges WHERE deleted_at IS NULL")->fetchColumn();
    echo "Totals => nodes: {$countNodes}, edges: {$countEdges}\n";
}

main();


