FROM python:3.13-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app
COPY requirements.txt ./requirements.txt
RUN python -m pip install --no-cache-dir -r requirements.txt
COPY medtwin_ai ./medtwin_ai
EXPOSE 8001
CMD ["uvicorn", "medtwin_ai.main:app", "--host", "0.0.0.0", "--port", "8001"]