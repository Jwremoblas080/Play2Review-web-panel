# 🎨 Enhanced Teacher Character Prompt for Discussion Panel

## 📋 Optimized AI Image Generation Prompt

### Main Prompt (Copy this to your AI image generator):

```
A warm and encouraging chibi-style female teacher character designed for an educational game interface. She has adorable cartoonish proportions with an oversized head (40% of total height) and a petite body, creating an approachable and non-intimidating presence. Her face features large, expressive sparkling eyes with gentle highlights that convey wisdom and kindness, soft rounded cheeks with a subtle blush, and a genuine warm smile that radiates encouragement and patience. 

She wears a professional yet cute teacher outfit: a fitted pastel blue blazer over a crisp white blouse with a small bow tie, paired with a knee-length navy skirt. Her stylish reading glasses rest slightly lowered on her nose, giving her an intelligent and approachable "ready to teach" look. In her right hand, she holds a small colorful textbook close to her chest, and in her left hand, a wooden pointer stick angled upward as if gesturing to explain a concept.

Her hair is medium-length, gently wavy with soft layers, colored in warm chestnut brown with subtle caramel highlights that catch the light. The hair has volume and movement, with a few strands playfully framing her face. Her pose is dynamic and welcoming: standing with a slight forward lean, head tilted 15 degrees to the right, one foot slightly forward, conveying enthusiasm and engagement. Her expression radiates "You can do this!" energy - encouraging, patient, and genuinely excited to help students learn.

Art style: Vibrant anime chibi aesthetic with clean vector-like line art, smooth gradient shading, and a soft pastel color palette (blues, whites, warm browns, soft pinks). Professional game asset quality with crisp edges, no texture noise, optimized for UI overlay. Lighting is soft and diffused from the front-top, creating gentle shadows that add depth without harshness. Background is a subtle gradient from soft sky blue to pale cream, or transparent PNG for easy integration.

Technical specifications: Ultra-clean 4K resolution (3840x2160), centered composition with 20% padding on all sides, character facing slightly toward viewer's right (3/4 view), optimized for 512x512 to 1024x1024 sprite usage, PNG format with alpha channel transparency.
```

---

## 🎯 Enhanced Structured Prompt (JSON Format)

```json
{
  "prompt": "Warm encouraging chibi female teacher, educational game character, oversized head (40% height), petite body, large sparkling expressive eyes with wisdom, soft rounded cheeks with subtle blush, genuine warm encouraging smile. Professional cute outfit: pastel blue blazer, white blouse with bow tie, navy knee-length skirt, stylish reading glasses lowered on nose. Holding colorful textbook (right hand) and wooden pointer stick (left hand, angled up). Medium-length wavy chestnut brown hair with caramel highlights, soft layers, volume and movement. Dynamic welcoming pose: slight forward lean, head tilted 15° right, one foot forward, enthusiastic teaching gesture. Expression radiates 'You can do this!' - encouraging, patient, excited to help. Vibrant anime chibi style, clean vector line art, smooth gradient shading, soft pastel palette (blues, whites, warm browns, soft pinks). Soft diffused front-top lighting, gentle shadows. Subtle gradient background (sky blue to pale cream) or transparent PNG. Ultra-clean 4K, centered with 20% padding, 3/4 view facing slightly right, optimized for 512x512-1024x1024 sprite, PNG alpha transparency.",
  
  "style": "chibi anime, kawaii, educational game asset, soft gradient shading, vibrant pastels, clean vector line art, professional UI quality",
  
  "character_details": {
    "gender": "female",
    "role": "encouraging teacher mentor",
    "age_appearance": "young adult (mid-20s)",
    "personality": "warm, patient, enthusiastic, supportive, wise",
    "expression": "genuinely encouraging smile, sparkling eyes, 'you can do this' energy",
    "pose": "dynamic teaching gesture, slight forward lean, head tilt, welcoming stance",
    "body_proportions": "chibi - oversized head 40%, small body, cute proportions"
  },
  
  "appearance": {
    "hair": {
      "length": "medium, shoulder-length",
      "style": "gently wavy with soft layers, volume and movement",
      "color": "warm chestnut brown with caramel highlights",
      "details": "few strands framing face, catches light beautifully"
    },
    "eyes": {
      "size": "large and expressive (chibi style)",
      "color": "warm brown or hazel with sparkle highlights",
      "expression": "kind, wise, encouraging, patient",
      "details": "gentle highlights, convey warmth and intelligence"
    },
    "face": {
      "shape": "soft rounded, gentle features",
      "cheeks": "soft with subtle blush",
      "smile": "genuine, warm, encouraging",
      "overall": "approachable, non-intimidating, trustworthy"
    },
    "accessories": [
      "stylish reading glasses (lowered on nose)",
      "small colorful textbook (held close to chest)",
      "wooden pointer stick (angled upward)",
      "small bow tie on blouse (optional)",
      "subtle earrings (optional)"
    ]
  },
  
  "outfit": {
    "top": "white blouse with small bow tie, pastel blue fitted blazer",
    "bottom": "navy knee-length skirt, professional cut",
    "style": "professional educator meets cute game character",
    "colors": "pastel blue, white, navy, coordinated and clean",
    "fit": "fitted but appropriate, emphasizes professional cute aesthetic"
  },
  
  "props": {
    "textbook": {
      "description": "small colorful educational book",
      "position": "held in right hand close to chest",
      "colors": "bright, inviting (red, blue, yellow accents)",
      "details": "visible title area, clean design"
    },
    "pointer_stick": {
      "description": "classic wooden teacher's pointer",
      "position": "left hand, angled upward at 45°",
      "color": "natural wood brown with white tip",
      "purpose": "teaching gesture, explaining concept"
    }
  },
  
  "pose_and_composition": {
    "stance": "standing, slight forward lean, one foot slightly forward",
    "head": "tilted 15 degrees to right, engaged and attentive",
    "arms": "right arm holding book to chest, left arm extended with pointer",
    "body_language": "open, welcoming, enthusiastic, ready to teach",
    "facing": "3/4 view, slightly toward viewer's right",
    "energy": "dynamic but not overwhelming, encouraging and supportive"
  },
  
  "rendering": {
    "quality": "ultra-clean 4K resolution (3840x2160)",
    "art_style": "vibrant anime chibi, professional game asset",
    "line_art": "clean vector-like lines, crisp edges, no texture noise",
    "shading": "smooth gradient shading, soft cel-shading technique",
    "colors": "soft pastel palette - blues, whites, warm browns, soft pinks",
    "lighting": "soft diffused from front-top, gentle shadows for depth",
    "edges": "crisp and clean, optimized for UI overlay",
    "format": "PNG with alpha channel transparency"
  },
  
  "background": {
    "option_1": "subtle gradient from soft sky blue (top) to pale cream (bottom)",
    "option_2": "transparent PNG for easy integration",
    "option_3": "very soft blurred classroom elements (books, chalkboard) in background",
    "preference": "transparent or minimal to not distract from character"
  },
  
  "technical_specs": {
    "resolution": "4K (3840x2160) for generation, scalable to 512x512 or 1024x1024",
    "composition": "centered with 20% padding on all sides",
    "format": "PNG with transparency",
    "color_space": "sRGB",
    "optimization": "suitable for Unity sprite import",
    "file_size": "optimized for game assets (under 2MB)",
    "usage": "UI overlay, discussion panel icon, educational interface"
  },
  
  "mood_and_atmosphere": {
    "overall_feeling": "warm, encouraging, supportive, educational",
    "emotional_tone": "you can do this, I believe in you, learning is fun",
    "target_audience": "middle school to high school students (ages 12-18)",
    "cultural_context": "universal educator, appropriate for Filipino educational setting",
    "message": "approachable mentor who makes learning feel safe and exciting"
  },
  
  "negative_prompt": "realistic, photorealistic, 3D render, low quality, blurry, distorted face, asymmetrical eyes, extra limbs, poorly drawn hands, malformed fingers, extra fingers, missing fingers, dark lighting, horror, scary, intimidating, stern expression, angry, sad, tired, unprofessional, revealing clothing, inappropriate, nsfw, watermark, signature, text, messy lines, pixelated, jpeg artifacts, noise, grainy, muddy colors, oversaturated, undersaturated, flat lighting, harsh shadows, cluttered background, busy composition, off-center, cropped, cut off, multiple characters, duplicate, deformed, mutation, bad anatomy, bad proportions, cloned face, disfigured, gross proportions, long neck, cross-eyed, lazy eye, uneven eyes, masculine features, old, elderly, child-like, too young, western cartoon style, Disney style, American cartoon, ugly, unattractive, plain, boring, generic, stock photo, clipart"
}
```

---

## 🎨 Alternative Variations for Different Subjects

### For Math/Science (Analytical Teacher)
```
Add: "holding a calculator and geometric compass, subtle mathematical symbols floating around, confident analytical expression, glasses slightly more prominent"
```

### For Filipino/AP (Cultural Teacher)
```
Add: "wearing traditional Filipino-inspired accessories (small flag pin, woven bracelet), warm Filipina features, holding a book with Filipino patterns, cultural pride in expression"
```

### For English/Literature (Creative Teacher)
```
Add: "holding a classic literature book and quill pen, slightly more artistic and creative expression, small sparkles around representing imagination"
```

---

## 📐 Unity Import Specifications

Once you have the generated image:

1. **File Format**: PNG with transparency
2. **Recommended Size**: 1024x1024 pixels
3. **Import Settings in Unity**:
   - Texture Type: Sprite (2D and UI)
   - Sprite Mode: Single
   - Pixels Per Unit: 100
   - Filter Mode: Bilinear
   - Compression: High Quality
   - Max Size: 1024 or 2048

4. **UI Placement**:
   - Position: Top-left of discussion panel
   - Size: 80x80 to 120x120 pixels in UI
   - Preserve Aspect: Enabled

---

## 🎯 AI Generator Recommendations

### Best Tools for This Style:

1. **Midjourney** (Best quality)
   - Use: `/imagine` + prompt
   - Add: `--ar 1:1 --style cute --quality 2`

2. **Leonardo.ai** (Free, great for anime)
   - Model: "Anime Pastel Dream" or "RPG 4.0"
   - Preset: "Anime"
   - Resolution: 1024x1024

3. **Stable Diffusion** (Most control)
   - Model: "Anything V5" or "Counterfeit V3"
   - Sampler: DPM++ 2M Karras
   - Steps: 30-40
   - CFG Scale: 7-9

4. **Bing Image Creator** (Free, easy)
   - Just paste the main prompt
   - Select "Creative" mode

---

## ✅ Quality Checklist

Before using the image, verify:
- [ ] Face is symmetrical and well-proportioned
- [ ] Eyes are expressive and properly aligned
- [ ] Hands are correctly drawn (5 fingers each)
- [ ] Outfit is professional and appropriate
- [ ] Colors are soft and inviting (not harsh)
- [ ] Background is clean or transparent
- [ ] Resolution is high enough (1024x1024 minimum)
- [ ] Expression is warm and encouraging
- [ ] Overall composition is centered and balanced
- [ ] No artifacts, watermarks, or text

---

## 🎨 Color Palette Reference

```
Primary Colors:
- Blazer: #A8D8EA (Soft Sky Blue)
- Blouse: #FFFFFF (Pure White)
- Skirt: #2C3E50 (Navy Blue)
- Hair: #8B6F47 (Chestnut Brown)
- Hair Highlights: #C19A6B (Caramel)
- Skin: #FFE4C4 (Warm Bisque)
- Blush: #FFB6C1 (Light Pink)

Accent Colors:
- Glasses Frame: #34495E (Dark Gray-Blue)
- Book Cover: #E74C3C (Warm Red) or #3498DB (Bright Blue)
- Pointer: #8B4513 (Saddle Brown)
- Background Gradient: #E8F4F8 to #FFF8E7

Eye Colors:
- Iris: #6B4423 (Warm Brown)
- Highlights: #FFFFFF (White sparkle)
```

---

## 💡 Pro Tips

1. **Generate Multiple Variations**: Create 3-5 versions and pick the best
2. **Test in Context**: Import to Unity and test in actual discussion panel
3. **Get Feedback**: Show to students/teachers for approval
4. **Consider Diversity**: Generate multiple versions (different ethnicities, ages)
5. **Backup Plan**: Keep 2-3 approved versions for variety

---

**Prompt Version**: 2.0 Enhanced
**Optimized For**: Educational Game Discussion Panel
**Target Style**: Chibi Anime Kawaii
**Created**: April 13, 2026
