<?php
require __DIR__.'/includes/bootstrap.php';

$slug=trim($_GET['slug']??'');
$subcategorySlug=trim($_GET['subcategory']??'');
$page=max(1,(int)($_GET['page']??1));
$perPage=12;
$category=null;$children=[];$activeChild=null;$rows=[];$total=0;

if($pdo&&$slug!==''){
    $s=$pdo->prepare('SELECT * FROM categories WHERE slug=? LIMIT 1');
    $s->execute([$slug]);
    $category=$s->fetch();
}

if(!$category){
    http_response_code(404);
    $title='Category not found';
    $description='The requested category is unavailable.';
}else{
    $isParent=$category['parent_id']===null;
    if($isParent){
        $s=$pdo->prepare('SELECT * FROM categories WHERE parent_id=? ORDER BY name');
        $s->execute([(int)$category['id']]);
        $children=$s->fetchAll();
    }

    if($subcategorySlug!==''&&$isParent){
        $s=$pdo->prepare('SELECT * FROM categories WHERE slug=? AND parent_id=? LIMIT 1');
        $s->execute([$subcategorySlug,(int)$category['id']]);
        $activeChild=$s->fetch()?:null;
        if(!$activeChild){
            http_response_code(404);
            $title='Subcategory not found';
            $description='The requested subcategory is unavailable.';
            $category=null;
        }
    }

    if($category){
        $title=$activeChild?$category['name'].' – '.$activeChild['name']:$category['name'];
        $description=$activeChild?($activeChild['description']?:('Articles about '.strtolower($activeChild['name']).'.')):($category['description']?:('Guides and resources about '.strtolower($category['name']).'.'));

        $where="a.status='published'";
        $params=[];
        if($activeChild){
            $where.=' AND a.category=? AND a.subcategory=?';
            $params=[$category['name'],$activeChild['name']];
        }elseif($isParent){
            $childNames=array_column($children,'name');
            $placeholders=implode(',',array_fill(0,count($childNames),'?'));
            $where.=' AND (a.category=?'.($childNames?' OR a.category IN ('.$placeholders.')':'').')';
            $params=array_merge([$category['name']],$childNames);
        }else{
            $parentName='';
            if($category['parent_id']!==null){
                $s=$pdo->prepare('SELECT name FROM categories WHERE id=? LIMIT 1');
                $s->execute([(int)$category['parent_id']]);
                $parentName=(string)($s->fetchColumn()?:'');
            }
            $where.=' AND (a.category=?'.($parentName!==''?' OR (a.category=? AND a.subcategory=?)':'').')';
            $params=$parentName!==''?[$category['name'],$parentName,$category['name']]:[$category['name']];
        }

        $count=$pdo->prepare('SELECT COUNT(*) FROM articles a WHERE '.$where);
        $count->execute($params);
        $total=(int)$count->fetchColumn();

        $query='SELECT a.* FROM articles a WHERE '.$where.' ORDER BY a.featured DESC,a.published_at DESC LIMIT '.(int)$perPage.' OFFSET '.(int)(($page-1)*$perPage);
        $s=$pdo->prepare($query);
        $s->execute($params);
        $rows=$s->fetchAll();

        foreach($children as &$child){
            $count=$pdo->prepare("SELECT COUNT(*) FROM articles WHERE status='published' AND category=? AND subcategory=?");
            $count->execute([$category['name'],$child['name']]);
            $child['article_count']=(int)$count->fetchColumn();
        }
        unset($child);

        $path=$slug.'/';
        $canonical=url($path.($activeChild?'?subcategory='.rawurlencode($activeChild['slug']):''));
        $list=[];
        foreach($rows as $row)$list[]=['name'=>$row['title'],'url'=>$row['canonical_url']?:url($slug.'/'.$row['slug'].'/')];
        $schema=item_list_schema($list);
        $crumbs=['Home'=>url(),$category['name']=>url($slug.'/')];
        if($activeChild)$crumbs[$activeChild['name']]=$canonical;
        $extra_schema=breadcrumb_schema($crumbs);
    }
}

$pages=(int)ceil($total/$perPage);
$paginationBase=$category?url($slug.'/'.($activeChild?'?subcategory='.rawurlencode($activeChild['slug']).'&':'')):'';
$prev_url=$page>1?$paginationBase.($activeChild?'&':'?').'page='.($page-1):null;
$next_url=$page<$pages?$paginationBase.($activeChild?'&':'?').'page='.($page+1):null;
require __DIR__.'/includes/header.php';
?>
<div class="container category-container">
 <p class="muted"><a href="<?=url()?>">Home</a><?php if($category):?> / <?=e($category['name'])?><?php endif;?><?php if($activeChild):?> / <?=e($activeChild['name'])?><?php endif;?></p>
 <h1><?=e($title)?></h1>
 <p><?=e($description)?></p>
 <?php if($category&&$isParent&&$children&&!$activeChild):?>
 <section class="category-children" aria-label="Subcategories">
  <h2>Explore <?=e($category['name'])?></h2>
  <div class="card-grid three">
   <?php foreach($children as $child):?><a class="card" href="<?=url($slug.'/?subcategory='.rawurlencode($child['slug']))?>"><h3><?=e($child['name'])?></h3><p><?=e($child['description']?:('Explore '.strtolower($child['name']).'.'))?></p><small><?=e((string)$child['article_count'])?> article(s)</small></a><?php endforeach;?>
  </div>
 </section>
 <?php endif;?>
 <?php if($activeChild):?><p><a href="<?=url($slug.'/')?>">&larr; All <?=e($category['name'])?></a></p><?php endif;?>
 <?php if($category):?><p class="muted"><?=e((string)$total)?> published article(s).</p><?php endif;?>
 <div class="grid">
  <?php foreach($rows as $article):?><article class="card">
   <?php if(!empty($article['featured_image'])):?><img loading="lazy" src="<?=e(media_url($article['featured_image']))?>" alt="<?=e($article['title'])?>"><?php endif;?>
   <p class="eyebrow"><?=e($article['subcategory']?:$category['name'])?></p>
   <h2><a href="<?=e($article['canonical_url']?:url($slug.'/'.$article['slug'].'/'))?>"><?=e($article['title'])?></a></h2>
   <p><?=e($article['excerpt'])?></p>
  </article><?php endforeach;?>
 </div>
 <?php if($category&&!$rows):?><p>No published articles found yet.</p><?php endif;?>
 <?php if($pages>1):?><nav aria-label="Category pagination"><?php for($i=1;$i<=$pages;$i++):?><a <?=$i===$page?'aria-current="page"':''?> href="<?=url($slug.'/'.($activeChild?'?subcategory='.rawurlencode($activeChild['slug']).'&':'?').'page='.$i)?>"><?=$i?></a> <?php endfor;?></nav><?php endif;?>
</div>
<?php require __DIR__.'/includes/footer.php';