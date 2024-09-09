int main()

{
setuid(0);
system("/var/projeto/services/inexclude.sh");
return 0;
}
