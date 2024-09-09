<?php

# inicia a sessão e define o usuário como autenticado, cookies
session_start();
# a função isset serve para verificar se o valor de uma variável não é nulla ou seja se login e senha != null o código adiciona o valor encontrado as variaveis $email e $senha
# caso contrário é retornada uma mensagem de erro
if (isset($_POST['email']) and isset($_POST['senha']))
{
	$email = $_POST['email'];
	$senha = $_POST['senha'];
	$_SESSION['senha'] = $senha;
	$_SESSION['email'] = $email;
}
else
	die('erro no envio dos par&acirc;metros!');
	
# a linha abaixo apenas é uma linha que facilita a visualização de erros que ocorrem no mysql, ela nao interfere diretamente no funcionamento do código só facilita visuzalização
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
# conecta ao estabelece conexão seguindo os padrões <servermysql>,<nomeuser>,<senhauser>,<nomeBD>.
$conexaoBD = new mysqli("192.168.102.100", "container26", "1F(450307)" ,"BD26");

# se houver um erro de conexão ao BD será retornada uma mensagem de erro e o tipo do erro logo em seguida
if ($conexaoBD->connect_errno)
{
	die("falha ao conectar ao MySQL: (" . $conexaoBD->connect_errno . ") " . $conexaoBD->connect_error);
}
$requisicaoBD = $conexaoBD->query("SELECT * from USERS where email='$email' and senha='$senha'");
if ($conexaoBD->errno)
{
	die("erro na execucao do SQL: $sql ($conexaoBD->errno) $conexaoBD->error");
};
# o comando fetch_assoc vai ler todas as linhas do BD e tentar identificar alguma que bata com o resultado da requisição do get $line
# se as informações baterem e o usuário estiver autenticado o login irá funcionar e ele será enviado para escoposite.php 
# se as informações não baterem o usuário vai retornar a página inicial de login e senha (indexlogin.php)
if ($line = $requisicaoBD->fetch_assoc()){
# se estiver autenticado seta var gid com base no gid da requisição ao bd
# soma mais 1 a seccao sempre que o user logar (interfere no codigo de trocar senha no 1 login)
	$conexaoBD->query("UPDATE USERS SET seccoes = 'antigo' WHERE email='$email'");
# pega o gid pra verificar o tipo da pagina que vai acessar e seta ele como var de sessao
	$dominio = $line['dominio'];
	$gid = $line['gid'];
	$seccoes = $line['seccoes'];
	$_SESSION['dominio'] = $dominio;
	$_SESSION['gid'] = $gid;
	$_SESSION["autenticado"]=1;
# define quantas seccoes esse user ja tem
# switch é tipo uma grande sequencia de elifs com base no valor de uma variavel especifica, nesse caso gid
	switch($gid){
		case '0':
			header("location: escopoadm.php"); break;
		case '1':
			if ($seccoes == 'primeiro') {
				header("location: primeirologin.php"); break;
			} else {
				header("location: escopoadmO.php"); break;
			}
		case '2':
			if ($seccoes == 'primeiro') {
				header("location: primeirologin.php"); break;
			} else {
				header("location: escopouser.php"); break;
			}
# se nao for nenhum desses gid (mesmo sendo praticamente impossivel sla tratei) redireciona pra pagina inicial
		default:
            header("location: indexlogin.php"); break;

	} 
	die();
} else {
# se nao autenticar ele só volta pra pag inicial
	$_SESSION["autenticado"] = 0;
	header("location: indexlogin.php");
	die();
}
?>

