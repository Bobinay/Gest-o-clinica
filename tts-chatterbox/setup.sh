#!/usr/bin/env bash
# Instala o Chatterbox (Resemble AI) num venv local. Linux/macOS.
set -euo pipefail
cd "$(dirname "$0")"

echo "== Verificando pré-requisitos =="
PY=""
for c in python3.11 python3.12 python3.10 python3; do
  if command -v "$c" >/dev/null; then PY="$c"; break; fi
done
[ -n "$PY" ] || { echo "ERRO: instale Python 3.10–3.12 (recomendado 3.11)"; exit 1; }
echo "Python: $($PY --version)"
command -v git >/dev/null || { echo "ERRO: instale o git"; exit 1; }
echo "Git: $(git --version)"
command -v ffmpeg >/dev/null && echo "ffmpeg: ok" || echo "AVISO: ffmpeg não encontrado (apt install ffmpeg / brew install ffmpeg)"
if ldconfig -p 2>/dev/null | grep -q libsndfile || [ -e /opt/homebrew/lib/libsndfile.dylib ] || [ -e /usr/local/lib/libsndfile.dylib ]; then
  echo "libsndfile: ok"
else
  echo "AVISO: libsndfile não encontrada (apt install libsndfile1 / brew install libsndfile)"
fi
command -v nvidia-smi >/dev/null && nvidia-smi --query-gpu=name,memory.total --format=csv,noheader || echo "GPU NVIDIA: não detectada (vai rodar em CPU/MPS)"

echo "== Clonando repositório oficial =="
[ -d chatterbox ] || git clone --depth 1 https://github.com/resemble-ai/chatterbox.git

echo "== Criando venv =="
[ -d .venv ] || "$PY" -m venv .venv
# shellcheck disable=SC1091
. .venv/bin/activate
pip install --upgrade pip

echo "== Instalando dependências (PyTorch 2.6 + Chatterbox) =="
pip install -e ./chatterbox

echo "== Baixando pesos do modelo (Hugging Face, ~3 GB) =="
python - <<'PY'
from huggingface_hub import snapshot_download
snapshot_download("ResembleAI/chatterbox")
print("Pesos em cache.")
PY

echo
echo "Pronto. Para testar:"
echo "  source $(pwd)/.venv/bin/activate"
echo "  python teste_tts.py"
