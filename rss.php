<?php
require __DIR__.'/includes/bootstrap.php';
header('Content-Type: application/rss+xml; charset=utf-8');
$items=$pdo?$pdo->query("SELECT title,slug,category,excerpt,published_at FROM articles WHERE status='published' AND noindex=0 ORDER BY published_at DESC LIMIT 20")->fetchAll():[];
echo '<?xml version="1.0" encoding="UTF-8"?>';
?><rss version="2.0"><channel><title><?=e($config['site_name'])?></title><link><?=e(url())?></link><description>Relaxing sounds for sleep, focus and calm.</description><?php foreach($items as $a):$category=strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$a['category']??''),'-'));$link=url(($category?$category.'/':'').$a['slug'].'/');?><item><title><?=e($a['title'])?></title><link><?=e($link)?></link><guid isPermaLink="true"><?=e($link)?></guid><description><?=e($a['excerpt']??'')?></description><pubDate><?=e(date(DATE_RSS,strtotime($a['published_at']??'now')))?></pubDate></item><?php endforeach;?></channel></rss>
