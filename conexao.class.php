<?

	Class Conexao{

		protected $host   = '127.0.0.1';
		protected $user   = 'belcomsa_pcsreis';
		protected $pswd   = '*982425677*';
		protected $dbname = 'belcomsa_mkt';
		protected $port   = '5432';
		protected $con    = null;		

		# +------------------------------------------------+
		# | método construtor                              |
		# +------------------------------------------------+
		function __construct(){
			
		}

		# +------------------------------------------------+
		# | ABRE CONEXÃO COM O BANCO DE DADOS              |
		# +------------------------------------------------+
		function Open(){
			$this->con = pg_connect("host=$this->host port=$this->port user=$this->user password=$this->pswd dbname=$this->dbname");
			return $this->con;
		}

		# +------------------------------------------------+
		# | FECHA CONEXÃO COM O BANCO DE DADOS             |
		# +------------------------------------------------+
		function Close(){
			@pg_close($this->con);
		}

		# +------------------------------------------------+
		# | VERIFICA SE EXISTE CONEXÃO INICIADA            |
		# +------------------------------------------------+
		function StatusCon(){
			return($this->con);
		}
	}
?>