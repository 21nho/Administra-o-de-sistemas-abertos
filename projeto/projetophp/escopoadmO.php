<?php
/* analisa com cookies de sessão se o usuário ainda esta autenticaod, se nao tiver ele volta pra pag inicial,
depois disso seta conexão com o BD */
session_start();
if (!($_SESSION["autenticado"]))
        header("location: indexlogin.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$bd = new mysqli("192.168.102.100", "container26", "1F(450307)" ,"BD26");
if ($bd->connect_errno)
{
	die("falha ao conectar ao MySQL: (" . $bd->connect_errno . ") " . $bd->connect_error);
}

/*EXCLUIR USER: se apertaram o botão ele pega a var usuario e logo em seguida faz uma requisição no bd
do tipo delete pra excluir a linha onde tiver aquilo que corresponde a var usuario puxada*/
if (isset($_POST['action']) && $_POST['action'] == 'excluir') {
    $user = $bd->real_escape_string($_POST['usuario']);
    $bd->query("DELETE FROM USERS WHERE email ='$user'");
    header("location: " . $_SERVER['PHP_SELF']);
    die();}
# FUNCIONA #

/* ADICIONAR NOVO USER: verifica se clicaram lá e se sim pega o que foi digitado na xcaixa de texto,
depois tem uma pequena função pra gerar uma senha aleatória. sempre que derem enter na caixa, caso o que foi
digitado na caixa de texto seja um user que não existe, ele é criado no modelo user@dominio
que possui senha aleatoria e gid = 2; caso o que foi digitado seja um user que ja existe simplesmente nao
acontecerá nada*/
if (isset($_POST['action']) && $_POST['action'] == 'adicionar') {
    $caixatexto = $bd->real_escape_string($_POST['caixatexto']);
    function gerarsenha($comprimento = 12) {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, $comprimento);
    }
    $senharandom = gerarsenha(12);
    $existe = $bd->query("SELECT dominios FROM USERSDOMINIOS WHERE dominios = '$caixatexto'");
    if (($existe->num_rows == 0) && (isset($_SESSION['email'])) && (isset($_SESSION['dominio']))) {
        $email = $_SESSION['email'];
        $dominioadmo = $_SESSION['dominio'];
        $bd->query("INSERT INTO USERS (email, senha, gid, ativo, dir, dominio, seccoes) VALUES ('$caixatexto@$dominioadmo', '$senharandom', '2', 's', '/root', '$email', 'primeiro')");
    }
    header("location: " . $_SERVER['PHP_SELF']);
    die();} # FUNCIONA #

/*MUDAR A PRÓRPRIA SENHA: verifica se apertaram o botão e pega o que digitaram na caixa de texto, depois pega a senha que o user
digitou la no indexlogin e usa ela como parametro pra achar a linha (desse user logado) la no bd e substituir ela, depois ele atualiza
a variavel de sessao pra nova senha*/
if (isset($_POST['action']) && $_POST['action'] == 'alterarsenha') {
    $propriasenha = $bd->real_escape_string($_POST['propriasenha']);
    if (isset($_SESSION['senha'])) {
        $senha = $_SESSION['senha'];
        $bd->query("UPDATE USERSDOMINIOS SET senhas = '$propriasenha' WHERE senhas='$senha'");
        $bd->query("UPDATE USERS SET senha = '$propriasenha' WHERE senha ='$senha'");
        $_SESSION['senha'] = $propriasenha;
    }
    header("location: " . $_SERVER['PHP_SELF']);
    die();} # FUNCIONA #

/* TROCAR A SENHA DO USUARIO: verifica se o botao foi apertado e seta as usuario da linha e senha q vai mudar, depois faz a
requisição pra att a tabela e trocar a senha*/
if (isset($_POST['action']) && $_POST['action'] == 'nvsenhauser') {
    $usuario = $bd->real_escape_string($_POST['user']);
    $novasenha = $bd->real_escape_string($_POST['novasenha']);
    $bd->query("UPDATE USERS SET senha = '$novasenha' WHERE email = '$usuario'");
    header("location: " . $_SERVER['PHP_SELF']);
    die();} # FUNCIONA #

$dominioadmo = $_SESSION['dominio'];
$tabela = $bd->query("SELECT * FROM USERS WHERE gid = 2"); # FUNCIONA #
?>

<!-- COMEÇO DO HTML/CSS o css eu só importei pro negocio nao ficar muito feio -->
<html>
<head>
    <TITLE>USERADM</TITLE>
<!-- CSS -->
<style>
    body {
        background-color: #F3E5F5; /* Lavanda bem clara */
        color: #6A1B9A; /* Roxo escuro para o texto */
     }

    table {
        border-collapse: collapse;
        width: auto; /* Ajusta a tabela ao tamanho do conteúdo */
        margin: 20px auto; /* Centraliza a tabela */
    }

    th, td {
        border: 1px solid #D1C4E9; /* Lilás muito claro */
        padding: 10px;
        text-align: left;
    }

    th {
            background-color: #E1BEE7; /* Lilás claro para o cabeçalho */
    }

    input[type="text"] {
        background-color: #F3E5F5; /* Lavanda bem clara */
        color: #6A1B9A; /* Roxo escuro para o texto */
        border: 1px solid #CE93D8; /* Lilás médio */
        padding: 5px;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"] {
        background-color: #E1BEE7; /* Lilás claro */
        color: #6A1B9A; /* Roxo escuro para o texto */
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"]:hover {
        background-color: #CE93D8; /* Lilás médio para hover */
    }
</style>
<!--------------javascript pra caixa de confirmação ao excluir--------------->
     <script>
        function desejaexcluir(event) {
            // impede o bagulhete de enviar insta qnd aperta o botao
            event.preventDefault(); 
            //se o caba apertar em enviar ele envia se nao nothing happened
            var confirmar = confirm("Deseja mesmo excluir este Usuário?");
            if (confirmar) {
                event.target.submit();
            }
        }
    </script>
<!-------------------------------------------------------->
<!--aqui volta a parte do html da exibição da tabela e da junção com o php-->
</head>
<body>
<h1>Página de usuário administrador, seja bem-vindo root@<?php echo $dominioadmo ?>!</h1>
    <table BORDER="1">
        <tr>
            <th>Usuários</th>
            <th>Senhas</th>
            <th>Alterar Senha do Usuário</th>
            <th>Remover Usuário</th>
        </tr>
        <?php while ($line = $tabela->fetch_assoc()) { ?>
            <?php if ($line['dominio'] == 'root@' . $dominioadmo) { ?>
            <tr>
                <td>
                    <input type="text" value="<?php echo $line['email']; ?>" readonly> 
                </td> 
                <td><input type="text" value="<?php echo $line['senha']; ?>" readonly></td> <!-- FUNCIONA-->
                <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <input type="hidden" name="user" value="<?php echo $line['email']; ?>">
                        <input type="text" name="novasenha" placeholder="Nova Senha" required>
                        <input type="hidden" name="action" value="nvsenhauser">
                        <input type="submit" value="Alterar"> <!--FUNCIONA-->
                    </form>
                </td>
                <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" onsubmit="desejaexcluir(event);">
                        <input type="hidden" name="usuario" value="<?php echo $line['email']; ?>">
                        <input type="hidden" name="action" value="excluir">
                        <input type="submit" value="Excluir"> <!-- FUNCIONA-->
                    </form>
                </td>
                <?php } ?>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="4" ALIGN="center">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <label for="caixatexto">Adicionar Novo Usuário:</label>
                    <input type="text" id="caixatexto" name="caixatexto" required>
                    <input type="hidden" name="action" value="adicionar">
                    <input type="submit" value="Adicionar"> <!-- FUNCIONA -->
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <label for="propriasenha">Alterar Própria Senha:</label>
                    <input type="text" id="propriasenha" name="propriasenha" required>
                    <input type="hidden" name="action" value="alterarsenha">
                    <input type="submit" value="Alterar"> <!-- FUNCIONA-->
                </form>
                <button type="button" onclick="window.location.href='sair.php'">SAIR</button> <!-- FUNCIONA-->
            </td>
        </tr>
    </table>
</body>
</html>


