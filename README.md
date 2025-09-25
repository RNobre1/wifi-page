# Portal de Acesso Wi-Fi

Este é um projeto de portal de acesso Wi-Fi simples e personalizável, ideal para estabelecimentos que desejam oferecer Wi-Fi gratuito aos seus clientes de forma estilizada e prática. A aplicação exibe as informações da rede e permite que os usuários copiem a senha com um único clique, sem a necessidade de exibi-la na tela.

## ✨ Funcionalidades

- **Design Moderno e Responsivo**: Interface limpa e agradável que se adapta a qualquer dispositivo.
- **Cópia de Senha Segura**: A senha do Wi-Fi é copiada para a área de transferência do usuário sem ser exibida, aumentando a segurança.
- **Fácil Personalização**: Altere o nome do patrocinador, o logotipo, o SSID da rede e a senha em um único arquivo de configuração.
- **Pronto para Deploy**: Configurado para deploy rápido e fácil na Netlify.

## 🚀 Começando

Siga estas instruções para obter uma cópia do projeto em funcionamento na sua máquina local para desenvolvimento e testes.

### Pré-requisitos

Você precisará ter o [Node.js](https://nodejs.org/) (que inclui o npm) instalado em sua máquina.

### Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/seu-repositorio.git
   ```
2. Navegue até o diretório do projeto:
   ```bash
   cd seu-repositorio
   ```
3. Instale as dependências:
   ```bash
   npm install
   ```

### Executando Localmente

Para iniciar o servidor de desenvolvimento, execute o seguinte comando:

```bash
npm run dev
```

Abra [http://localhost:5173](http://localhost:5173) (ou a porta indicada no seu terminal) no seu navegador para ver a aplicação.

## 🎨 Personalização

Toda a configuração da aplicação pode ser encontrada no arquivo `constants.ts`. Abra este arquivo e edite as variáveis para personalizar o portal com as informações do seu estabelecimento.

```typescript
// src/constants.ts

// O nome do seu estabelecimento que aparecerá em destaque.
export const SPONSOR_NAME = 'Nome da Sua Empresa';

// A URL do logotipo do seu estabelecimento.
export const SPONSOR_LOGO_URL = 'https://caminho.para/seu/logo.png';

// O nome da rede Wi-Fi (SSID) que seus clientes devem procurar.
export const WIFI_SSID = 'SuaRedeWiFi';

// A senha da sua rede Wi-Fi.
export const WIFI_PASSWORD = 'SuaSenhaSuperSecreta';
```

## ☁️ Deploy na Netlify

Este projeto está pronto para ser implantado na [Netlify](https://www.netlify.com/).

### Passos para o Deploy

1. **Faça um Fork deste Repositório**: Clique no botão "Fork" no canto superior direito desta página para criar uma cópia do repositório na sua conta do GitHub.

2. **Crie uma Conta na Netlify**: Se você ainda não tiver uma, crie uma conta gratuita na Netlify.

3. **Crie um Novo Site a partir do Git**:
   - No seu dashboard da Netlify, clique em "Add new site" -> "Import an existing project".
   - Conecte sua conta do GitHub.
   - Escolha o repositório que você acabou de "forkar".

4. **Configurações de Build**: A Netlify detectará automaticamente as configurações corretas a partir do arquivo `netlify.toml` incluído no projeto. As configurações são:
   - **Build command**: `npm run build`
   - **Publish directory**: `dist`

5. **Clique em "Deploy site"**: A Netlify fará o build e o deploy do seu site. Em poucos minutos, seu portal de Wi-Fi estará online!

## 🛠️ Tecnologias Utilizadas

- [React](https://reactjs.org/) - Biblioteca para construir interfaces de usuário.
- [Vite](https://vitejs.dev/) - Ferramenta de build para o frontend.
- [TypeScript](https://www.typescriptlang.org/) - Superset de JavaScript que adiciona tipagem estática.
- [Tailwind CSS](https://tailwindcss.com/) - Framework de CSS utilitário para design rápido.