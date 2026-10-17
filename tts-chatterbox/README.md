# Chatterbox TTS (Resemble AI) — instalação local

Chatterbox é o TTS open source (licença MIT) da **Resemble AI**: <https://github.com/resemble-ai/chatterbox>.
Ele não é da ElevenLabs, que não publica modelos open source. Suporta 23 idiomas, incluindo português (`pt`), e clonagem de voz a partir de um clipe de ~10 s.

## Pré-requisitos
- Python 3.10 a 3.12 (recomendado **3.11**) e Git
- ffmpeg e libsndfile (`sudo apt install ffmpeg libsndfile1` / `brew install ffmpeg libsndfile`)
- ~8 GB de disco (PyTorch + ~3 GB de pesos). GPU NVIDIA ou Apple Silicon é opcional, mas em CPU a geração é lenta.

## Instalar
```bash
cd tts-chatterbox
./setup.sh            # Linux/macOS
# .\setup.ps1         # Windows PowerShell
```
O script verifica os pré-requisitos, clona o repositório oficial em `chatterbox/`, cria o venv `.venv/`, instala as dependências e baixa os pesos para o cache do Hugging Face (`~/.cache/huggingface`).

## Testar
```bash
source .venv/bin/activate
python teste_tts.py                                   # gera teste_pt.wav
python teste_tts.py --texto "Bom dia!" --saida bom_dia.wav
python teste_tts.py --voz minha_voz.wav               # clonagem de voz
```
Se aparecer `OK -> teste_pt.wav`, a instalação funcionou. Ouça o arquivo para confirmar.

O áudio gerado inclui a marca d'água inaudível Perth da Resemble AI.
