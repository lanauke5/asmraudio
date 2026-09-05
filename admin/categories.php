<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();

$error='';
$edit=['id'=>0,'name'=>'','slug'=>'','parent_id'=>null,'description'=>''];
if($pdo&&isset($_GET['edit'])){
    $s=$pdo->prepare('SELECT * FROM categories WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit=$s->fetch()?:$edit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'';
    $id=(int)($_POST['id']??0);
    if($action==='delete'&&$id&&$pdo){
        $pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
        header('Location: categories.php');
        exit;
    }
    if($action==='save'){
        $name=trim($_POST['name']??'');
        $slug=trim($_POST['slug']??'');
        $description=trim($_POST['description']??'');
        $parentId=(int)($_POST['parent_id']??0);
        $edit=['id'=>$id,'name'=>$name,'slug'=>$slug,'parent_id'=>$parentId?:null,'description'=>$description];
        if($name===''||!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$slug))$error='Enter a name and a valid lowercase slug.';
        elseif($parentId===$id&&$id)$error='A category cannot be its own parent.';
        elseif($pdo){
            try{
                if($parentId){
                    $s=$pdo->prepare('SELECT 1 FROM categories WHERE id=? AND parent_id IS NULL');
                    $s->execute([$parentId]);
                    if(!$s->fetchColumn())throw new RuntimeException('Select a valid parent category.');
                }
                if($id)$pdo->prepare('UPDATE categories SET name=?,slug=?,parent_id=?,description=? WHERE id=?')->execute([$name,$slug,$parentId?:null,$description,$id]);
                else $pdo->prepare('INSERT INTO categories(name,slug,parent_id,description) VALUES(?,?,?,?)')->execute([$name,$slug,$parentId?:null,$description]);
                header('Location: categories.php');
                exit;
            }catch(Throwable $e){$error=$e instanceof RuntimeException?$e->getMessage():'Unable to save category; the slug may already exist.';}
        }
    }
}

$categories=$pdo?$pdo->query('SELECT c.*,p.name AS parent_name FROM categories c LEFT JOIN categories p ON p.id=c.parent_id ORDER BY COALESCE(p.name,c.name),c.parent_id IS NOT NULL,c.name')->fetchAll():[];
$parents=array_values(array_filter($categories,static fn($category)=>$category['parent_id']===null));
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Categories</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><header><a class="brand" href="dashboard.php">ASMR Audio Admin</a><nav><a href="articles.php">Articles</a><a href="categories.php">Categories</a><a href="logout.php">Log out</a></nav></header><main class="container"><h1>Categories</h1><p class="muted">Create a parent category or choose one parent for a subcategory.</p><?php if($error):?><p role="alert"><?=e($error)?></p><?php endif;?><form method="post" class="card"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$edit['id']?>"><p><label>Parent category<select name="parent_id"><option value="">None (parent category)</option><?php foreach($parents as $parent):if((int)$parent['id']===(int)$edit['id'])continue;?><option value="<?=$parent['id']?>" <?=($edit['parent_id']??null)==$parent['id']?'selected':''?>><?=e($parent['name'])?></option><?php endforeach;?></select></label></p><p><label>Name<input name="name" value="<?=e($edit['name'])?>" required></label></p><p><label>Slug<input name="slug" value="<?=e($edit['slug'])?>" required></label></p><p><label>Description<textarea name="description"><?=e($edit['description']??'')?></textarea></label></p><button>Save category</button><?php if($edit['id']):?> <a href="categories.php">Cancel</a><?php endif;?></form><section class="card" style="overflow:auto"><table style="width:100%"><tr><th>Category</th><th>Slug</th><th>Parent</th><th>Actions</th></tr><?php foreach($categories as $category):?><tr><td><?=$category['parent_id']!==null?'↳ ':''?><?=e($category['name'])?></td><td><?=e($category['slug'])?></td><td><?=e($category['parent_name']??'—')?></td><td><a href="?edit=<?=$category['id']?>">Edit</a> <form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$category['id']?>"><button type="submit">Delete</button></form></td></tr><?php endforeach;?></table></section></main></body></html>