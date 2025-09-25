
import React, { useState, useCallback } from 'react';
import { SPONSOR_NAME, SPONSOR_LOGO_URL, WIFI_SSID, WIFI_PASSWORD } from '../constants';

const WifiCard: React.FC = () => {
  const [isCopied, setIsCopied] = useState<boolean>(false);

  const handleCopyPassword = useCallback(() => {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(WIFI_PASSWORD).then(() => {
        setIsCopied(true);
        setTimeout(() => {
          setIsCopied(false);
        }, 3000);
      }).catch(err => {
        console.error('Failed to copy password: ', err);
        alert('Não foi possível copiar a senha.');
      });
    } else {
        alert('A cópia para a área de transferência não é suportada neste navegador.');
    }
  }, []);

  return (
    <div className="bg-gray-800 text-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center flex flex-col items-center transform transition-all duration-300 hover:scale-105">
      <img
        src={SPONSOR_LOGO_URL}
        alt="Sponsor Logo"
        className="w-24 h-24 rounded-full border-4 border-indigo-500 object-cover mb-4 shadow-lg"
      />
      <h1 className="text-xl font-semibold text-gray-300">Wi-Fi patrocinado por</h1>
      <h2 className="text-3xl font-bold text-indigo-400 mb-6">{SPONSOR_NAME}</h2>
      
      <div className="bg-gray-700/50 rounded-lg p-4 w-full mb-6">
        <p className="text-sm text-gray-400 mb-2">Conecte-se à rede:</p>
        <p className="text-lg font-mono bg-gray-900/50 py-2 px-4 rounded-md text-green-400">{WIFI_SSID}</p>
      </div>

      <p className="text-gray-300 mb-4 text-md">
        Clique abaixo para copiar a senha. A senha não será exibida por segurança.
      </p>

      <button
        onClick={handleCopyPassword}
        disabled={isCopied}
        className={`w-full py-4 px-6 rounded-lg font-bold text-lg transition-all duration-300 ease-in-out transform focus:outline-none focus:ring-4 ${
          isCopied
            ? 'bg-green-600 text-white cursor-not-allowed focus:ring-green-500/50'
            : 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500/50 active:scale-95'
        }`}
      >
        {isCopied ? (
          <div className="flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
            Senha Copiada!
          </div>
        ) : (
          'Copiar Senha para Conectar'
        )}
      </button>
      
      <p className="text-xs text-gray-500 mt-8">
        Após copiar, vá para as configurações de Wi-Fi, selecione a rede e cole a senha.
      </p>
    </div>
  );
};

export default WifiCard;
