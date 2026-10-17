# Instala o Chatterbox (Resemble AI) num venv local. Windows (PowerShell).
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "== Verificando pré-requisitos =="
py -3.11 --version; if ($LASTEXITCODE) { throw "Instale Python 3.11 (python.org) com o 'py launcher'" }
git --version
if (-not (Get-Command ffmpeg -ErrorAction SilentlyContinue)) { Write-Warning "ffmpeg não encontrado (winget install ffmpeg)" }
if (Get-Command nvidia-smi -ErrorAction SilentlyContinue) { nvidia-smi --query-gpu=name,memory.total --format=csv,noheader } else { Write-Host "GPU NVIDIA não detectada (vai rodar em CPU)" }

if (-not (Test-Path chatterbox)) { git clone --depth 1 https://github.com/resemble-ai/chatterbox.git }
if (-not (Test-Path .venv)) { py -3.11 -m venv .venv }
.\.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
# Com GPU NVIDIA, instale antes o torch com CUDA:
#   pip install torch==2.6.0 torchaudio==2.6.0 --index-url https://download.pytorch.org/whl/cu124
pip install -e .\chatterbox
python -c "from huggingface_hub import snapshot_download; snapshot_download('ResembleAI/chatterbox'); print('Pesos em cache.')"

Write-Host "`nPronto. Para testar:  .\.venv\Scripts\Activate.ps1 ; python teste_tts.py"
