<?php
session_start();
$_SESSION['autenticado'] = 0;
?>
<HTML>
<head>
<style>
        body {
            background-color: #F3E5F5; /* Lavanda muito clara */
            color: #6A1B9A; /* Roxo escuro */
        }
        table {
            border-collapse: collapse; 
        }
        th, td {
            padding: 10px;
        }
        input[type="text"], input[type="password"] {
            background-color: #EDE7F6; /* Lilás muito claro */
            color: #6A1B9A; /* Roxo escuro */
            border: 1px solid #D1C4E9; /* Lilás claro */
        }
        input[type="submit"] {
            background-color: #D1C4E9; /* Lilás claro */
            color: #6A1B9A; /* Roxo escuro */
            border: none; 
            padding: 10px 20px; 
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #B39DDB; /* Lilás médio para hover */
        }
</style>
    <title>Projeto</title>
</head>
<body>
<div ALIGN="center">
    <h1># Projeto ASA #</h1>
    <h2>Insira suas credenciais</h2>

    <table BORDER="3">
        <form action="loginanalise.php" method="POST">
            <tr>
                <th>E-mail</th>
                <td><input type="text" name="email"></td>
            </tr>
            <tr>
                <th>Senha</th>
                <td><input type="password" name="senha"></td>
            </tr>
            <tr>
                <td colspan="2" ALIGN="center">
                    <input type="submit" value="LOGIN">
                </td>
            </tr>
        </form>
    </table>
</div>
