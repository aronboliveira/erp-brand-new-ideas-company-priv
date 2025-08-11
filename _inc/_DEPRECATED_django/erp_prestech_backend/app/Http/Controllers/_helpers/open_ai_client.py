# libs/openai_client.py

import openai

class OpenAiClient:
    def __init__(self, api_key: str):
        openai.api_key = api_key

    def generate_completion(self, prompt, temperature=0.7, max_tokens=150, n=1, model="text-davinci-003"):
        return openai.Completion.create(
            model=model,
            prompt=prompt,
            temperature=temperature,
            max_tokens=max_tokens,
            n=n
        )
