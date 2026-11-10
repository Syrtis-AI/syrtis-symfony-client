import AgentConversation from '@wexample/symfony-ai-conversation/Entity/AgentConversation';
import schema from '../data/entity/syrtis_conversation.json';

// A conversation held by Syrtis, told apart by its `kind`. Written by hand,
// its parent living in symfony-ai-conversation.
export default class SyrtisConversation extends AgentConversation {
  static retrieveEntitySchema() {
    return schema;
  }
}
