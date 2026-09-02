@echo off
echo SCRIPT .BAT para compilar o projeto em PRODUCAO
echo "---------------------------------------------"
pause

rem Apaga os arquivos javascripts compilados da última versão para gerar os novos.

del /q ".\public\js\chunks\*"
FOR /D %%p IN (".\public\js\chunks\*.*") DO rmdir "%%p" /s /q

rem Apaga os arquivos de cache do node_modules, estes arquivos não são necessários e aumentam a pasta compactada.

del /q ".\node_modules\.cache\*"
FOR /D %%p IN (".\node_modules\.cache\*.*") DO rmdir "%%p" /s /q

npm run prod