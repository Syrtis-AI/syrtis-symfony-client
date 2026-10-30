import ConversationSession from '@wexample/symfony-ai-conversation/Entity/ConversationSession';
import schema from '../data/entity/syrtis_conversation_session.json';

// A conversation held by Syrtis: a session to the api and the live topics,
// told apart by its `engine`. Written by hand, its parent living in
// symfony-ai-conversation.
export default class SyrtisConversationSession extends ConversationSession {
  static retrieveEntitySchema() {
    return schema;
  }
}
