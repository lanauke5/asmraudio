<?php
function save_upload(array $file,string $kind):?string{
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)return null;
    $rules=['image'=>['jpg','jpeg','png','webp','image/jpeg','image/png','image/webp',5*1024*1024],'audio'=>['mp3','wav','ogg','audio/mpeg','audio/wav','audio/ogg',50*1024*1024]];
    if(!isset($rules[$kind])||($file['size']??0)>$rules[$kind][6])throw new RuntimeException('File is too large or invalid.');
    $finfo=new finfo(FILEINFO_MIME_TYPE);$mime=$finfo->file($file['tmp_name']);$ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));if(!in_array($mime,array_slice($rules[$kind],3,3),true)||!in_array($ext,array_slice($rules[$kind],0,3),true))throw new RuntimeException('Invalid file type.');
    $dir=__DIR__.'/../uploads/'.$kind;if(!is_dir($dir))mkdir($dir,0755,true);$name=bin2hex(random_bytes(16)).'.'.$ext;if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name))throw new RuntimeException('Unable to store upload.');return 'uploads/'.$kind.'/'.$name;
}
