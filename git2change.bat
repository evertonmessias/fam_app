echo @ECHO OFF

rem cp $(git ls-files --modified) ../modified-files

mkdir changes

FOR /F %%I IN ('git ls-files --modified') DO XCOPY /t "./%%I" "./changes/%%I"
GOTO END

:END