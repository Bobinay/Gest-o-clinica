"""Teste rápido do Chatterbox (Resemble AI): gera um .wav em português.

Uso:
    python teste_tts.py                         # voz padrão
    python teste_tts.py --voz referencia.wav    # clona a voz de um clipe de ~10s
    python teste_tts.py --texto "Olá!" --saida ola.wav
"""
import argparse
import time

import torch
import torchaudio as ta
from chatterbox.mtl_tts import ChatterboxMultilingualTTS


def escolher_dispositivo():
    if torch.cuda.is_available():
        return "cuda"
    if torch.backends.mps.is_available():
        return "mps"
    return "cpu"


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--texto", default="Olá! Sua consulta está confirmada para amanhã às dez horas.")
    p.add_argument("--idioma", default="pt")
    p.add_argument("--voz", help="clipe .wav de referência para clonagem de voz")
    p.add_argument("--saida", default="teste_pt.wav")
    args = p.parse_args()

    device = escolher_dispositivo()
    print(f"PyTorch {torch.__version__} | dispositivo: {device}")

    t0 = time.time()
    # Na primeira execução baixa os pesos do Hugging Face (~3 GB, fica em cache)
    model = ChatterboxMultilingualTTS.from_pretrained(device=device)
    print(f"Modelo carregado em {time.time() - t0:.1f}s")

    t0 = time.time()
    wav = model.generate(args.texto, language_id=args.idioma, audio_prompt_path=args.voz)
    duracao = wav.shape[-1] / model.sr
    print(f"Gerado {duracao:.1f}s de áudio em {time.time() - t0:.1f}s")

    assert wav.numel() > 0 and torch.isfinite(wav).all(), "áudio vazio ou inválido"
    ta.save(args.saida, wav, model.sr)
    print(f"OK -> {args.saida} ({model.sr} Hz)")


if __name__ == "__main__":
    main()
