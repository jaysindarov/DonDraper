---
name: Image Generation Architecture
description: Config-driven Strategy pattern for multi-model AI image generation; adding a model = editing config/ai_models.php only
type: project
---

The image generation backend uses a config-driven provider pattern:

- `config/ai_models.php` — single source of truth for every model: driver class, async flag, prompt templates, valid sizes, defaults
- `App\Contracts\ImageGenerationProvider` — interface all providers implement (OpenAiGptImageProvider, OpenAiDallEProvider, ReplicateProvider)
- `App\Services\ImageGeneration\ImageProviderFactory` — resolves provider from config via Laravel container
- `App\Services\ImageGeneration\PromptBuilder` — applies per-model prompt templates from config using {placeholder} interpolation
- `App\Services\ImageGeneration\VisionAnalyzer` — GPT-4o vision analysis for reference persons and product images
- `App\Services\ImageGeneration\ImageStorageService` — stores base64 or URL images to `public` disk
- `App\Services\ImageGeneration\GenerationResult` — immutable value object: `completed(localUrl)` or `pending(predictionId)`
- `App\Jobs\ProcessImageGeneration` — thin orchestrator only; no provider or prompt logic

**Why:** User plans to add more AI models; this lets them add a model by editing config/ai_models.php only — no job code changes.

**How to apply:** When user asks to add a new image model, add it to `config/ai_models.php` with the appropriate `driver` class and prompt templates. If it's a new provider type (not OpenAI/Replicate), create a new class implementing `App\Contracts\ImageGenerationProvider`.

The old `app/Services/ReplicateImageProvider.php` is superseded but kept in place (no other references).
