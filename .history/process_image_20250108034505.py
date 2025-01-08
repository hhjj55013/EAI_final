import sys
import torch
from torchvision import transforms, models
import torch.nn as nn
from PIL import Image

# 確保提供了圖片路徑參數
if len(sys.argv) < 2:
    print("必須提供圖片路徑")
    sys.exit(1)

image_path = sys.argv[1]

# 載入模型
model = models.resnet34(weights="DEFAULT")
model.fc = nn.Linear(512, 1)
model.load_state_dict({k.replace('module.', ''): v for k, v in torch.load("best_model.pth", map_location="cpu").items()})
model.eval()

# 定義圖片轉換
transform = transforms.Compose([
    transforms.ToPILImage(),
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.5, 0.5, 0.5], std=[0.5, 0.5, 0.5]),
])

# 讀取圖片並進行預處理
try:
    image = Image.open(image_path).convert("RGB")
    input_image = transform(image).unsqueeze(0)

    # 推論
    with torch.no_grad():
        output = model(input_image)
    score = output.item() * 20

    # 輸出分數到標準輸出
    print(f"{score:.2f}")
except Exception as e:
    print(f"處理圖片失敗: {str(e)}", file=sys.stderr)
    sys.exit(1)
