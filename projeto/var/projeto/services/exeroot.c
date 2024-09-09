int main()

{
setuid(0);
system("/var/projeto/services/reiniciar_apachebind");
return 0;
}
