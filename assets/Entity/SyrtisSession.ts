import Session from '@wexample/symfony-ai/Entity/Session';
import schema from '../data/entity/syrtis_session.json';

// A conversation held by Syrtis: a session to the api and the live topics,
// told apart by its `engine`. Written by hand, its parent living in
// symfony-ai.
export default class SyrtisSession extends Session {
  static retrieveEntitySchema() {
    return schema;
  }
}
