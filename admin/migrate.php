<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();

$done=[];$errors=[];
if($pdo&&$_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations(version VARCHAR(80) PRIMARY KEY,applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $migrations=[
            '001_add_featured_image'=>static function(PDO $pdo):void{
                $has=$pdo->query("SHOW COLUMNS FROM articles LIKE 'featured_image'")->fetchColumn();
                if(!$has)$pdo->exec('ALTER TABLE articles ADD COLUMN featured_image VARCHAR(500) NULL AFTER category');
            },
            '002_hierarchical_asmr_categories'=>static function(PDO $pdo):void{
                $hasParent=$pdo->query("SHOW COLUMNS FROM categories LIKE 'parent_id'")->fetchColumn();
                if(!$hasParent)$pdo->exec('ALTER TABLE categories ADD COLUMN parent_id INT NULL AFTER slug');
                $hierarchy=[
                    ['ASMR Sleep','asmr-sleep','Sleep-focused ASMR, bedtime routines, and calming audio.',[['ASMR for Deep Sleep','asmr-for-deep-sleep','ASMR choices for a calmer deep-sleep routine.'],['Bedtime ASMR','bedtime-asmr','Gentle ASMR for winding down before bed.'],['Sleep Sounds','sleep-sounds','Relaxing sounds and ASMR for sleep.'],['Insomnia Relaxation','insomnia-relaxation','Low-stimulation relaxation ideas for difficult nights.']]],
                    ['ASMR Triggers','asmr-triggers','Explore popular ASMR triggers and listening styles.',[['Whispering','whispering','Whispering ASMR and close, quiet voice content.'],['Tapping','tapping','Gentle tapping triggers and textures.'],['Scratching','scratching','Soft scratching sounds and trigger guides.'],['Brushing','brushing','Brush sounds and soft sweeping textures.'],['Personal Attention','personal-attention','Comforting personal-attention ASMR.'],['Ear-to-Ear / Binaural','ear-to-ear-binaural','Spatial ear-to-ear and binaural ASMR audio.'],['Roleplay','roleplay','Relaxing ASMR roleplay scenarios.']]],
                    ['Relaxation & Stress Relief','relaxation-stress-relief','Calming ASMR for unwinding, focus, and everyday stress relief.',[['Calming ASMR','calming-asmr','Gentle ASMR for a calmer atmosphere.'],['Anxiety Relaxation','anxiety-relaxation','Calming ASMR routines for general relaxation.'],['Meditation ASMR','meditation-asmr','Quiet ASMR for reflective, mindful listening.'],['Focus & Study','focus-study','Steady ASMR and ambience for focus and study.'],['Stress Relief Sounds','stress-relief-sounds','Soothing sounds for a slower-paced routine.']]],
                    ['ASMR Sounds','asmr-sounds','Ambient and non-verbal ASMR sound collections.',[['Rain Sounds','rain-sounds','Rain ambience and gentle water sounds.'],['White Noise','white-noise','Steady white-noise listening options.'],['Brown Noise','brown-noise','Deep, low-frequency brown-noise listening options.'],['Nature Sounds','nature-sounds','Outdoor ambience and natural soundscapes.'],['Ambient Sounds','ambient-sounds','Calm ambient audio for relaxation.']]],
                    ['ASMR Guides','asmr-guides','Practical, beginner-friendly ASMR guidance.',[['Beginner Guides','beginner-guides','A welcoming place to start with ASMR.'],['How ASMR Works','how-asmr-works','Explanations of ASMR and common responses.'],['Headphones & Volume','headphones-volume','Listening setup and volume guidance.'],['Listening Tips','listening-tips','Helpful ways to build an ASMR routine.'],['ASMR Safety','asmr-safety','Responsible, comfortable ASMR listening.']]],
                    ['ASMR Creators','asmr-creators','Creator profiles, channels, and artist resources.',[['Creator Profiles','creator-profiles','Profiles of ASMR creators.'],['Best ASMR Channels','best-asmr-channels','Helpful ASMR channel recommendations.'],['Recommended Artists','recommended-artists','ASMR artists worth exploring.'],['Creator Interviews','creator-interviews','Conversations with ASMR creators.']]],
                    ['ASMR Equipment','asmr-equipment','Listening and recording equipment for ASMR.',[['Headphones','headphones','Headphone guides for ASMR listeners.'],['Earbuds','earbuds','Earbud options for ASMR listening.'],['Microphones','microphones','Microphone guides for ASMR recording.'],['Recording Gear','recording-gear','Useful ASMR recording equipment.'],['Sleep Audio Devices','sleep-audio-devices','Devices designed for bedtime audio.']]],
                ];
                $pdo->beginTransaction();
                try{
                    $upsertParent=$pdo->prepare("INSERT INTO categories(name,slug,parent_id,description) VALUES(?,?,NULL,?) ON DUPLICATE KEY UPDATE name=VALUES(name),parent_id=NULL,description=IF(description IS NULL OR description='',VALUES(description),description)");
                    $selectBySlug=$pdo->prepare('SELECT id FROM categories WHERE slug=? LIMIT 1');
                    $upsertChild=$pdo->prepare("INSERT INTO categories(name,slug,parent_id,description) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),parent_id=VALUES(parent_id),description=IF(description IS NULL OR description='',VALUES(description),description)");
                    foreach($hierarchy as [$parentName,$parentSlug,$parentDescription,$children]){
                        $upsertParent->execute([$parentName,$parentSlug,$parentDescription]);
                        $selectBySlug->execute([$parentSlug]);
                        $parentId=(int)$selectBySlug->fetchColumn();
                        foreach($children as [$childName,$childSlug,$childDescription])$upsertChild->execute([$childName,$childSlug,$parentId,$childDescription]);
                    }
                    $maps=[
                        ['best-asmr-sounds-for-deep-sleep','ASMR Sleep','ASMR for Deep Sleep'],
                        ['asmr-for-anxiety-relief','Relaxation & Stress Relief','Anxiety Relaxation'],
                        ['what-is-asmr','ASMR Guides','Beginner Guides'],
                        ['how-does-asmr-work','ASMR Guides','How ASMR Works'],
                        ['tapping-asmr-for-sleep','ASMR Triggers','Tapping'],
                        ['best-asmr-sounds-for-sleep','ASMR Sleep','Sleep Sounds'],
                        ['best-asmr-triggers-relaxation','ASMR Triggers',''],
                        ['do-you-need-headphones-for-asmr','ASMR Equipment','Headphones'],
                        ['start-asmr-youtube-channel','ASMR Creators','Best ASMR Channels'],
                        ['whispering-asmr','ASMR Triggers','Whispering'],
                        ['asmr-for-focus-and-studying','Relaxation & Stress Relief','Focus & Study'],
                        ['rain-sounds-for-sleeping','ASMR Sounds','Rain Sounds'],
                        ['white-noise-vs-brown-noise','ASMR Sounds','White Noise'],
                        ['best-headphones-for-asmr','ASMR Equipment','Headphones'],
                        ['best-asmr-microphones-for-beginners','ASMR Equipment','Microphones'],
                    ];
                    $updateArticle=$pdo->prepare('UPDATE articles SET category=?,subcategory=? WHERE slug=?');
                    foreach($maps as [$articleSlug,$category,$subcategory])$updateArticle->execute([$category,$subcategory,$articleSlug]);
                    $pdo->commit();
                }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
            },
            '003_publish_best_sleep_audio_devices'=>static function(PDO $pdo):void{
                $sql=file_get_contents(__DIR__.'/../database/article-best-sleep-audio-devices.sql');
                if($sql===false)throw new RuntimeException('Article migration file is unavailable.');
                $pdo->exec($sql);
            },
        ];
        foreach($migrations as $version=>$migration){
            $s=$pdo->prepare('SELECT 1 FROM schema_migrations WHERE version=?');
            $s->execute([$version]);
            if($s->fetchColumn())continue;
            $migration($pdo);
            $pdo->prepare('INSERT INTO schema_migrations(version) VALUES(?)')->execute([$version]);
            $done[]=$version;
        }
    }catch(Throwable $e){$errors[]='Migration failed. No category data was changed.';}
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Database Migration</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><main class="container"><section class="card"><h1>Database Migration</h1><?php foreach($done as $version):?><p role="status">Applied <?=e($version)?></p><?php endforeach;?><?php foreach($errors as $error):?><p role="alert"><?=e($error)?></p><?php endforeach;?><p>This safe migration adds the ASMR category hierarchy and maps existing articles by slug. It does not delete tables, categories, or articles.</p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><button>Run migrations</button></form></section></main></body></html>