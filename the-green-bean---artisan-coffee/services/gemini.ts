
import { GoogleGenAI } from "@google/genai";

const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

export const getBaristaAdvice = async (mood: string): Promise<string> => {
  try {
    const response = await ai.models.generateContent({
      model: 'gemini-3-flash-preview',
      contents: `You are an expert artisan barista at 'The Green Bean'. A customer is feeling '${mood}'. 
      Suggest a coffee drink and a pastry from our menu. 
      Menu Items: Golden Espresso, Honey Lavender Latte, Cold Brew Reserve, Almond Croissant, Chocolate Muffin, Avocado Toast.
      Keep it short, charming, and professional.`,
      config: {
        systemInstruction: "You are a charming, expert barista who loves artisan coffee and fresh green beans.",
        temperature: 0.7,
      }
    });
    return response.text || "I recommend our Signature Espresso to wake up your senses!";
  } catch (error) {
    console.error("Gemini Error:", error);
    return "The machines are steaming! I suggest a classic Latte while I reset.";
  }
};
