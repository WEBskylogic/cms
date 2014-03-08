<?
class Feedback extends Model
{
    static $table='feedback'; //Главная талица
    static $name='Обратная связь'; // primary key
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

}
?>