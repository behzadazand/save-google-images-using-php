<?php 
require_once ('user.php');
require_once ('database.php');
class Search{
    use baseQueries;
    protected $search_input;
    public $images_array;
    protected $fillable=['title','path','path_thumb','mime','snippet','search_input'];
    protected $dh;
    protected $db; 
    protected $table="images";
public function __construct(){
    $this->joincolumn=join(' , ',$this->fillable);  
}
     public function search_input($search_input){
          $this->search_input=$search_input;
         $this->search_query(); 
    }
    protected function search_query(){    
    $url ="https://www.googleapis.com/customsearch/v1?key=YOUR_GOOGLE_API&cx=YOUR_CSE_ID&q={$this->search_input}&num=10&searchType=image";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curl_close($curl);
    $this->images_array= json_decode($data,true);
    }
    public function show_image(){
    $num=0;
    echo '<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post" name="form2">';
        $images=array_chunk($this->images_array['items'], 3);
        
        foreach($images as $three_images){
            echo '<div class="row">';
            foreach ($three_images as $image ){
               echo '<div class="col-md-4">'; 
                echo '<img src="' . $image['image']['thumbnailLink'] . '" alt="' . $image['title'] . '" width="' . $image['image']['thumbnailWidth'] . '" height="' . $image['image']['thumbnailHeight'] . '" />';
                echo '<p>'.$image['title'].'</p>';
                $arr=[$this->search_input,$image['title'],$image['snippet'],$image['link'],$image['mime'],$image['image']['thumbnailLink']];
                $value=join("#",$arr);
                echo "<input name='selected[]' type='checkbox' value='".$value."' >".$num;
                $num++;
                
               echo '</div>';
            }
            echo '</div>';
        }
        echo '<input type="submit" name="save_selected" class="btn btn-lg button-theme" value="ذخیره تصاویر">';
        echo '</form>';
    } 
    
public function save_images($selected){
     
    $image_directory="images/base/";
    $thumb_directory="images/thumbnail/"; 
    $error=""; 
    if(!file_exists($image_directory)) {
    mkdir($image_directory, 0777, true);
     }
    if(!file_exists($thumb_directory)) {
        mkdir($thumb_directory, 0777, true);
    } 
    foreach ($selected as $select){
        list($input,$title,$snippet,$link,$mime,$thumbnailLink) = explode('#', $select);
        $x=time();
        $exp = explode(".",$link);
        $extension = end($exp);
        $path=$image_directory.$x.rand(0,100).'.'.$extension;
        print_r($path);
        $path_thumb=$thumb_directory.$x.rand(0,100).'.'.$extension;
        print_r($path_thumb);
        $link = file_get_contents($link);
        $thumbnailLink = file_get_contents($thumbnailLink);
        if(file_put_contents($path,$link)&&file_put_contents($path_thumb,$thumbnailLink)){
             $values=[$title,$user_id,$path,$path_thumb,$mime,$snippet,$input];
        try{
            $this->db = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME,DB_USER, DB_PASSWORD, array(
            PDO::ATTR_PERSISTENT => true , PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
       ));
        
        foreach($this->fillable as $feild){
            $bparam[]= ":".$feild;
        }
    
        $fields = '( ' . $this->joincolumn . ' )';
        $bound = '(' . join(' , ',$bparam). ' )';
        $sq= $fields.' VALUES '.$bound;
        echo "<br>";
        print_r($sq);
        echo "<br>";
        $sql="insert into {$this->table} {$sq}";
        print_r($sql);
        $stmt=$this->db->prepare($sql);
        $p=array_combine($bparam,$values);
        print_r($p);
        return $stmt->execute($p);
        }catch(Exception $e){
           echo  $error=$e->getMessage();
        }
        }     
        
        }//end foreach
    
    }//end save_images
}

?>
