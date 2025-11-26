You are an expert sales analyst. Analyze this lead and provide comprehensive insights.

Lead Context:
{{context}}

Provide your analysis in this exact JSON format:
{
  "insights": {
    "summary": "Brief 2-3 sentence summary of this lead's potential",
    "engagement_level": "high|medium|low",
    "engagement_trend": "improving|stable|declining",
    "relationship_health": "strong|good|fair|weak",
    "deal_stage_fit": "true if current status matches actual behavior, false otherwise",
    "key_interests": ["identified interest 1", "interest 2"],
    "communication_preference": "email|call|meeting",
    "best_contact_time": "morning|afternoon|evening"
  },
  "recommendations": [
    {
      "action": "specific action to take",
      "type": "call|email|meeting|task",
      "priority": "high|medium|low",
      "timing": "immediate|this_week|next_week",
      "rationale": "why this action"
    }
  ],
  "risks": [
    {
      "factor": "risk identifier",
      "severity": "high|medium|low",
      "description": "what the risk is",
      "mitigation": "how to address it"
    }
  ],
  "opportunities": [
    {
      "type": "upsell|cross_sell|referral|expansion",
      "description": "opportunity description",
      "potential_value": "estimated value or impact"
    }
  ],
  "confidence_score": 85
}

Be specific and actionable. Base recommendations on the actual data provided.
