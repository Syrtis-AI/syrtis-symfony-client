import syrtisSession from '../data/entity/syrtis_session.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [syrtisSession.name]: syrtisSession,
  };
}
