<?

class Testing extends Model

{

    static $table='testing'; //Главная талица

    static $name='Тикеты'; // primary key

	

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