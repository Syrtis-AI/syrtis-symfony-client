import syrtisConversation from '../data/entity/syrtis_conversation.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [syrtisConversation.name]: syrtisConversation,
  };
}
