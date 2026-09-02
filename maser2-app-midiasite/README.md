# Aplicativo Maser Transportes

Aplicativo desenvolvido para a empresa Maser Transportes.

## Android

### Compatibilidade

###### Atualizado 05/11/2025

| Nome                          | Versão                          | 
|-------------------------------|---------------------------------|
| **Ionic**                     |                                 |
| Ionic CLI                     | 7.2.0                           |
| Ionic Framework               | @ionic/angular 8.7.8            |
| @angular-devkit/build-angular | 17.3.17                         |
| @angular-devkit/schematics    | 17.3.17                         |
| @angular/cli                  | 17.3.17                         |
| @ionic/angular-toolkit        | 12.3.0                          |
| **Capacitor**                 |                                 |
| Capacitor CLI                 | 7.4.4                           |
| @capacitor/android            | 7.4.4                           |
| @capacitor/core               | 7.4.4                           |
| **System**                    |                                 | 
| NodeJS                        | 22.20.0                         |
| npm                           | 10.9.3                          |
| Android Studio                | Narwhal 3 Feature Drop 2025.1.3 | 

`ionic info`

### Setup

1. Instalar Git

2. Instalar Node.js (npm já incluso)

3. Instalar Microsoft Visual Studio Code

4. Instalar Android Studio

5. Instalar Java

* É necessário instalar o JDK (Java Development Kit), pois o Android Studio usa o Java para compilar o código.
* Recomenda-se o JDK 21, pois é compatível com as versões recentes do Android Studio.

6. Download Gradle

* O Gradle geralmente é gerenciado automaticamente pelo Android Studio. Ele baixa e usa a versão necessária para o seu projeto.
* No entanto, para o Ionic, você pode precisar de uma instalação local do Gradle para rodar comandos como "npx cap sync android" ou "npx cap open android".

7. Configurar variáveis de ambiente do sistema:

| Variável         | Valor                                 |
|------------------|---------------------------------------|
| JAVA_HOME        | C:\Program Files\Java\jdk-21          |
| GRADLE_HOME      | C:\Gradle\gradle-8.11.1               |
| ANDROID_SDK_ROOT | C:\Users\%USUARIO%\AppData\Local\Android\Sdk ou C:\android-sdk-windows |
| Path             | %JAVA_HOME%\bin                       |
| Path             | %GRADLE_HOME%\bin                     |
| Path             | %ANDROID_SDK_ROOT%\build-tools\35.0.0 |
| Path             | %ANDROID_SDK_ROOT%\emulator           |
| Path             | %ANDROID_SDK_ROOT%\platform-tools     |
| Path             | %ANDROID_SDK_ROOT%\tools\bin          |

### Instalação

Instalar dependências:
```
npm install
```

Rodar app (localhost):
```
ionic serve
```

Compilar aplicação:
```
ionic build
```

Executar aplicação no dispositivo:
`No Android Studio -> Run 'App'`


### Gerar versão de produção (Android Play Store Deployment)

[Documentação](https://ionicframework.com/docs/deployment/play-store)

#### Passo 1 - Compilar

Copiar todos os recursos da Web e sincronizar quaisquer alterações de plug-in. 
Necessário apenas quando algum plugin for instalado ou alguma grande alteração for feita no app.
```
ionic cap sync
```

Para gerar uma compilação de lançamento, executar o seguinte comando CLI:
```
ionic cap build android --prod
```

#### Passo 2 - Abrir Android Studio

Abrir o projeto no Android Studio:
```
ionic cap open android
```
No Android Studio, atualizar Gradle (se necessário):

1. Se ocorrer o erro "Incompatible Gradle JVM", no painel Build, clicar no link "Upgrade to Gradle 8.9 and re-sync".
2. Se aparecer o popup "Project update recommended" Clicar em "Start AGP Upgrade Assistant". Depois em "Run selected steps".

#### Passo 3 - Ajustar versão do app e nível desejado da API (SDK Android)

Para ajustar a versão do app e garantir que a Google Play Store aceite o AAB/APK atualizado, siga os passos abaixo:

1. Abra o arquivo **android/app/build.gradle** no seu projeto.
2. Localize a seção android e, em seguida, a subseção defaultConfig.
3. Incrementar o valor da propriedade **versionCode** (por exemplo, aumente de 20007 para 20008).
4. Atualize o valor da propriedade **versionName** para a nova versão (por exemplo, de "2.0.7" para "2.0.8").
5. Salve as alterações no arquivo build.gradle.

Para ajustar o nível desejado da API (SDK Android), siga os passos abaixo:

1. Abra o arquivo **android/variables.gradle** no seu projeto.
2. Atualize o valor da propriedade **compileSdkVersion** para a versão desejada (por exemplo, de 33 para 34).
3. Atualize o valor da propriedade **targetSdkVersion** para a versão desejada (por exemplo, de 33 para 34).
4. Salve as alterações no arquivo variables.gradle.

#### Passo 4 - Assinar

No Android Studio:

1. No menu abrir Build
2. Escolher a opção **Generate Signed Bundle / APK**
3. Seguir as instruções para assinar o AAB (Android App Bundle) com seu arquivo de armazenamento de chaves **<project-root>/key_store/maser-app-key.keystore**.
4. Se tudo ocorrer bem até aqui, você terá o arquivo de produção **<project-root>/android/app/release/app-release.aab**. Fazer upload deste arquivo na Google Play Console.


## Anotações de desenvolvimento

### Dependências

##### Listar e comparar todas as dependências do projeto com as mais recentes
```
npm outdated
```
**Current →** versão que está instalada.  
**Wanted →** versão mais recente **que respeita a regra** do seu package.json (^, ~, etc).  
**Latest →** última versão estável publicada no npm (mesmo que quebre compatibilidade).

##### Atualizar todas as dependências para a versão Wanted
```
npm update
```
Esse comando:  
Atualiza todas as libs do package.json **até o limite permitido pela regra de versão.**  
Exemplo: se no package.json está "^5.2.1", ele vai atualizar até a **última 5.x disponível**, mas nunca para 6.x.


##### Atualizar uma dependência específica para a versão Wanted
```
npm update <nome-do-pacote>
```

### Plugins instalados

###### Atualizado 05/11/2025
```
[info] Found 6 Capacitor plugins for android:
       @capacitor/app@7.1.0
       @capacitor/barcode-scanner@2.2.0
       @capacitor/camera@7.0.2
       @capacitor/haptics@7.0.2
       @capacitor/keyboard@7.0.3
       @capacitor/status-bar@7.0.3
[info] Found 7 Cordova plugins for android:
       cordova-plugin-advanced-http@3.3.1
       cordova-plugin-app-version@0.1.14
       cordova-plugin-device@2.1.0
       cordova-plugin-file@8.1.3
       cordova-plugin-screen-orientation@3.0.4
       es6-promise-plugin@4.2.2
       onesignal-cordova-plugin@3.3.3
[info] Listing plugins for web is not possible.
```

`npx cap ls` -> Listar todos os plugins Cordova e Capacitor instalados.

### Plugin OneSignal

##### Personalizar sons das notificações (Android)

Os arquivos de som (.wav, .mp3, .ogg) devem ser adicionados na pasta:
```
<project-root>/platforms/android/app/src/main/res/raw
```

Arquivo de som default:
```
onesignal_default_sound.mp3
```