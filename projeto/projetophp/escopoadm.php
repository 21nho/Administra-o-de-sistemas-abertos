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

/*EXCLUIR DOMINIO: se apertaram o botão ele pega a var domain e logo em seguida faz uma requisição no bd
do tipo delete pra excluir a linha onde tiver aquilo que corresponde a var domain puxada*/
if (isset($_POST['action']) && $_POST['action'] == 'excluir') {
    $domain = $bd->real_escape_string($_POST['domain']);
    $bd->query("DELETE FROM USERSDOMINIOS WHERE dominios ='$domain'");
    $bd->query("DELETE FROM USERS WHERE dominio ='$domain'");
    header("Location: " . $_SERVER['PHP_SELF']);
    shell_exec("/var/projeto/services/exerootine");
    die();
}

/* ADICIONAR NOVO DOMINIO: verifica se clicaram lá e se sim pega o que foi digitado na xcaixa de texto,
depois tem uma pequena função pra gerar uma senha aleatória. sempre que derem enter na caixa, caso o que foi
digitado na caixa de texto seja um domínio que não existe ele é criado junto com um usuário root@dominio
que possui senha aleatoria e gid = 1; caso o que foi digitado seja um dominio que ja existe simplesmente nao
acontecerá nada*/
if (isset($_POST['action']) && $_POST['action'] == 'adicionar') {
    $caixatexto = $bd->real_escape_string($_POST['caixatexto']);
    function gerarsenha($comprimento = 12) {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, $comprimento);
    }
    $senharandom = gerarsenha(12);
    $existe = $bd->query("SELECT dominios FROM USERSDOMINIOS WHERE dominios = '$caixatexto'");
    if ($existe->num_rows == 0) {
        $bd->query("INSERT INTO USERSDOMINIOS (dominios, senhas) VALUES ('$caixatexto', '$senharandom')");
        $bd->query("INSERT INTO USERS (email, senha, gid, ativo, dir, dominio, seccoes) VALUES ('root@$caixatexto', '$senharandom', '1', 's', '/root', '$caixatexto', 'primeiro')");
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    shell_exec("/var/projeto/services/exerootine");
    die();}

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
    header("Location: " . $_SERVER['PHP_SELF']);
    die();}

/* TROCAR A SENHA DO DOMINIO: verifica se o botao foi apertado e seta as variaveis dominio e senha q vai mudar, depois faz a
requisição pra att as duas tabelas, tanto a de exibição quanto a principal e att a pagina*/
if (isset($_POST['action']) && $_POST['action'] == 'nvsenhadominio') {
    $dominio = $bd->real_escape_string($_POST['dominio']);
    $novasenha = $bd->real_escape_string($_POST['novasenha']);
    $bd->query("UPDATE USERSDOMINIOS SET senhas = '$novasenha' WHERE dominios = '$dominio'");
    $bd->query("UPDATE USERS SET senha = '$novasenha' WHERE dominio = '$dominio'");
    header("Location: " . $_SERVER['PHP_SELF']);
    die();}

$tabela = $bd->query("SELECT * FROM USERSDOMINIOS");
?>

<!-- COMEÇO DO HTML/CSS o css eu só importei pro negocio nao ficar muito feio -->
<html>
<head>
    <TITLE>USERADM</TITLE>
<!-- CSS -->
<style>
    body {
        background-color: #E3F2FD; /* Azul claro muito suave */
        color: #0D47A1; /* Azul escuro para o texto */
    }

    table {
        border-collapse: collapse;
        width: auto; /* Ajusta a tabela ao tamanho do conteúdo */
        margin: 20px auto; /* Centraliza a tabela */
    }

    th, td {
        border: 1px solid #BBDEFB; /* Azul muito claro */
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #90CAF9; /* Azul claro para o cabeçalho */
    }

    input[type="text"] {
        background-color: #E3F2FD; /* Azul claro muito suave */
        color: #0D47A1; /* Azul escuro para o texto */
        border: 1px solid #90CAF9; /* Azul claro para a borda */
        padding: 5px;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"] {
        background-color: #90CAF9; /* Azul claro para o botão */
        color: #0D47A1; /* Azul escuro para o texto */
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 4px; /* Adiciona bordas arredondadas */
    }

    input[type="submit"]:hover {
        background-color: #64B5F6; /* Azul médio para hover */
    }
</style>
<!--------------javascript pra caixa de confirmação ao excluir--------------->
     <script>
        function desejaexcluir(event) {
            // impede o bagulhete de enviar insta qnd aperta o botao
            event.preventDefault(); 
            //se o caba apertar em enviar ele envia se nao nothing happened
            var confirmar = confirm("Deseja mesmo excluir este domínio?");
            if (confirmar) {
                event.target.submit();
            }
        }
    </script>
<!-------------------------------------------------------->
<!--aqui volta a parte do html da exibição da tabela e da junção com o php-->
</head>
<body>
<h1>Página de usuário administrador, seja bem-vindo!</h1>
    <table BORDER="1">
        <tr>
            <th>Domínios</th>
            <th>Senha do root</th>
            <th>Alterar Senha do root</th>
            <th>Remover domínios</th>
        </tr>
        <?php while ($line = $tabela->fetch_assoc()) { ?>
            <tr>
                <td><input type="text" value="<?php echo $line['dominios']; ?>" readonly></td>
                <td><input type="text" value="<?php echo $line['senhas']; ?>" readonly></td>
                <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <input type="hidden" name="dominio" value="<?php echo $line['dominios']; ?>">
                        <input type="text" name="novasenha" placeholder="Nova Senha" required>
                        <input type="hidden" name="action" value="nvsenhadominio">
                        <input type="submit" value="Alterar">
                    </form>
                </td>
                <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" onsubmit="desejaexcluir(event);">
                        <input type="hidden" name="domain" value="<?php echo $line['dominios']; ?>">
                        <input type="hidden" name="action" value="excluir">
                        <input type="submit" value="Excluir">
                    </form>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="4" ALIGN="center">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <label for="caixatexto">Adicionar Novo Domínio:</label>
                    <input type="text" id="caixatexto" name="caixatexto" required>
                    <input type="hidden" name="action" value="adicionar">
                    <input type="submit" value="Adicionar">
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <label for="propriasenha">Alterar Própria Senha:</label>
                    <input type="text" id="propriasenha" name="propriasenha" required>
                    <input type="hidden" name="action" value="alterarsenha">
                    <input type="submit" value="Alterar">
                </form>
                <button type="button" onclick="window.location.href='sair.php'">SAIR</button>
            </td>
        </tr>
    </table>
</body>
</html>

