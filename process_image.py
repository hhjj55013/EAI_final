import sys
import os
import torch
import warnings
from torchvision import models, transforms
import torch.nn as nn
from thop import profile
from PIL import Image
device = "cuda:0" if torch.cuda.is_available() else "cpu"
sys.stdout = open(os.devnull, 'w')
warnings.simplefilter(action='ignore', category=FutureWarning)

# 設定 TORCH_HOME 環境變數
os.environ["TORCH_HOME"] = "./torch_cache"
if not os.path.exists("./torch_cache"):
    os.makedirs("./torch_cache")

# 確保提供了圖片路徑參數
if len(sys.argv) < 2:
    print("必須提供圖片路徑")
    sys.exit(1)

image_path = sys.argv[1]

# 載入模型
def load_model():
    model = models.resnet34(weights="DEFAULT")
    model.fc = nn.Linear(512, 1)

    # 模型資訊和統計
    random_data = torch.randn(1, 3, 350, 350)
    profile(model, inputs=(random_data,))

    # 加載訓練好的權重
    state_dict = torch.load("best_model.pth", map_location=device)
    model.load_state_dict({k.replace('module.', ''): v for k, v in state_dict.items()})
    model.to(device)
    model.eval()

    return model

model = load_model()

transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.5, 0.5, 0.5], std=[0.5, 0.5, 0.5]),
])

# 讀取圖片並進行預處理
try:
    image = Image.open(image_path).convert("RGB")
    input_image = transform(image).unsqueeze(0).to(device)

    # 推論
    with torch.no_grad():
        output = model(input_image)
    score = output.item() * 20

    sys.stdout = sys.__stdout__
    # 輸出分數到標準輸出
    print(f"{score:.2f}")
except Exception as e:
    print(f"處理圖片失敗: {str(e)}", file=sys.stderr)
    sys.exit(1)
