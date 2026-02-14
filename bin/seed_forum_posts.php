<?php
// Simple seed script to create test posts with different createdAt dates
use App\Kernel;
use App\Entity\Post;


// Load environment variables (DATABASE_URL etc.)
$require = dirname(__DIR__) . '/vendor/autoload.php';
require $require;

// Load .env for CLI if Dotenv is available
if (class_exists(\Symfony\Component\Dotenv\Dotenv::class)) {
    $envPath = dirname(__DIR__) . '/.env';
    if (file_exists($envPath)) {
        (new \Symfony\Component\Dotenv\Dotenv())->loadEnv($envPath);
    }
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();
$doctrine = $container->get('doctrine');
$em = $doctrine->getManager();

$samples = [
    ['title' => 'Post 2026-02-01', 'content' => 'Content A', 'author' => 'UserA', 'date' => '2026-02-01 10:00:00', 'tags' => 'tag1,tag2'],
    ['title' => 'Post 2026-02-05', 'content' => 'Content B', 'author' => 'UserB', 'date' => '2026-02-05 14:30:00', 'tags' => 'tag2'],
    ['title' => 'Post 2026-02-08 morning', 'content' => 'Content C', 'author' => 'Anonyme', 'date' => '2026-02-08 08:15:00', 'tags' => 'tag1'],
    ['title' => 'Post 2026-02-08 night', 'content' => 'Content D', 'author' => 'UserC', 'date' => '2026-02-08 22:45:00', 'tags' => 'tag3'],
    ['title' => 'Post 2026-01-15', 'content' => 'Old post', 'author' => 'UserD', 'date' => '2026-01-15 09:00:00', 'tags' => 'tag1,tag3'],
    ['title' => 'Post 2025-12-31', 'content' => 'Year end', 'author' => 'UserE', 'date' => '2025-12-31 23:59:00', 'tags' => 'tag4'],
];

foreach ($samples as $row) {
    $p = new Post();
    $p->setTitle($row['title']);
    $p->setContent($row['content']);
    $p->setAuthor($row['author']);
    $p->setTags($row['tags']);
    $p->setCreatedAt(new \DateTimeImmutable($row['date']));
    // leave image/reactions default
    $em->persist($p);
}

$em->flush();

echo "Seeded " . count($samples) . " posts.\n";

// Graceful kernel shutdown
$kernel->shutdown();
