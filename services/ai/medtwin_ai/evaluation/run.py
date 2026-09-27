import json
from pathlib import Path

from medtwin_ai.evaluation.synthetic_experiments import run_synthetic_experiments


def main() -> None:
    result = run_synthetic_experiments()
    print(json.dumps(result, indent=2))


if __name__ == "__main__":
    main()