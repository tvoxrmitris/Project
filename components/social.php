class Social extends Model
{
public $timestamps = false;
protected $fillable = [
&#39;provider_user_id&#39;, &#39;provider&#39;, &#39;user&#39;
];

protected $primaryKey = &#39;user_id&#39;;
protected $table = &#39;tbl_social&#39;;
public function login(){
return $this-&gt;belongsTo(&#39;App\Login&#39;, &#39;user&#39;);
}
}